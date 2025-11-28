const Transaction = require('../models/Transaction');
const Fund = require('../models/Fund');
const { TRANSACTION_TYPES, TRANSACTION_STATUS } = require('../config/constants');

// @desc    Get all transactions
// @route   GET /api/transactions
// @access  Private
exports.getTransactions = async (req, res, next) => {
  try {
    const page = parseInt(req.query.page, 10) || 1;
    const limit = parseInt(req.query.limit, 10) || 10;
    const startIndex = (page - 1) * limit;

    const query = {};

    // Filter by fund
    if (req.query.fund) {
      query.fund = req.query.fund;
    }

    // Filter by user
    if (req.query.user) {
      query.user = req.query.user;
    }

    // Filter by type
    if (req.query.type) {
      query.type = req.query.type;
    }

    // Filter by status
    if (req.query.status) {
      query.status = req.query.status;
    }

    // Filter by date range
    if (req.query.startDate || req.query.endDate) {
      query.createdAt = {};
      if (req.query.startDate) {
        query.createdAt.$gte = new Date(req.query.startDate);
      }
      if (req.query.endDate) {
        query.createdAt.$lte = new Date(req.query.endDate);
      }
    }

    const total = await Transaction.countDocuments(query);
    const transactions = await Transaction.find(query)
      .populate('fund', 'name')
      .populate('user', 'username email')
      .populate('fromUser', 'username')
      .populate('toUser', 'username')
      .populate('processedBy', 'username')
      .skip(startIndex)
      .limit(limit)
      .sort({ createdAt: -1 });

    res.status(200).json({
      success: true,
      data: {
        transactions,
        pagination: {
          current: page,
          pageSize: limit,
          total,
          pages: Math.ceil(total / limit)
        }
      }
    });
  } catch (error) {
    next(error);
  }
};

// @desc    Get single transaction
// @route   GET /api/transactions/:id
// @access  Private
exports.getTransaction = async (req, res, next) => {
  try {
    const transaction = await Transaction.findById(req.params.id)
      .populate('fund', 'name')
      .populate('user', 'username email')
      .populate('fromUser', 'username')
      .populate('toUser', 'username')
      .populate('processedBy', 'username');

    if (!transaction) {
      return res.status(404).json({
        success: false,
        message: '交易记录不存在'
      });
    }

    res.status(200).json({
      success: true,
      data: transaction
    });
  } catch (error) {
    next(error);
  }
};

// @desc    Create transaction
// @route   POST /api/transactions
// @access  Private
exports.createTransaction = async (req, res, next) => {
  try {
    const { type, amount, fund, user, description, fromUser, toUser } = req.body;

    // Get fund details
    const fundDoc = await Fund.findById(fund);
    if (!fundDoc) {
      return res.status(404).json({
        success: false,
        message: '资金盘不存在'
      });
    }

    const balanceBefore = fundDoc.availableAmount;
    let balanceAfter = balanceBefore;

    // Calculate balance after based on transaction type
    switch (type) {
      case TRANSACTION_TYPES.DEPOSIT:
        balanceAfter = balanceBefore + amount;
        break;
      case TRANSACTION_TYPES.WITHDRAW:
        if (amount > balanceBefore) {
          return res.status(400).json({
            success: false,
            message: '余额不足'
          });
        }
        balanceAfter = balanceBefore - amount;
        break;
      case TRANSACTION_TYPES.PROFIT:
        balanceAfter = balanceBefore + amount;
        break;
      case TRANSACTION_TYPES.LOSS:
        balanceAfter = balanceBefore - amount;
        break;
      default:
        break;
    }

    const transaction = await Transaction.create({
      type,
      amount,
      fund,
      user: user || req.user.id,
      description,
      fromUser,
      toUser,
      balanceBefore,
      balanceAfter,
      status: TRANSACTION_STATUS.PENDING
    });

    res.status(201).json({
      success: true,
      message: '交易创建成功',
      data: transaction
    });
  } catch (error) {
    next(error);
  }
};

// @desc    Process transaction (approve/reject)
// @route   PUT /api/transactions/:id/process
// @access  Private/Admin
exports.processTransaction = async (req, res, next) => {
  try {
    const { status } = req.body;

    if (!Object.values(TRANSACTION_STATUS).includes(status)) {
      return res.status(400).json({
        success: false,
        message: '无效的状态'
      });
    }

    const transaction = await Transaction.findById(req.params.id);

    if (!transaction) {
      return res.status(404).json({
        success: false,
        message: '交易记录不存在'
      });
    }

    if (transaction.status !== TRANSACTION_STATUS.PENDING) {
      return res.status(400).json({
        success: false,
        message: '该交易已处理'
      });
    }

    // If approving, update fund balance
    if (status === TRANSACTION_STATUS.COMPLETED) {
      const fund = await Fund.findById(transaction.fund);

      if (!fund) {
        return res.status(404).json({
          success: false,
          message: '资金盘不存在'
        });
      }

      switch (transaction.type) {
        case TRANSACTION_TYPES.DEPOSIT:
          fund.availableAmount += transaction.amount;
          fund.totalAmount += transaction.amount;
          break;
        case TRANSACTION_TYPES.WITHDRAW:
          if (transaction.amount > fund.availableAmount) {
            return res.status(400).json({
              success: false,
              message: '余额不足'
            });
          }
          fund.availableAmount -= transaction.amount;
          fund.totalAmount -= transaction.amount;
          break;
        case TRANSACTION_TYPES.PROFIT:
          fund.availableAmount += transaction.amount;
          fund.totalAmount += transaction.amount;
          break;
        case TRANSACTION_TYPES.LOSS:
          fund.availableAmount -= transaction.amount;
          fund.totalAmount -= transaction.amount;
          break;
        default:
          break;
      }

      await fund.save();
    }

    transaction.status = status;
    transaction.processedAt = Date.now();
    transaction.processedBy = req.user.id;
    await transaction.save();

    res.status(200).json({
      success: true,
      message: status === TRANSACTION_STATUS.COMPLETED ? '交易已批准' : '交易已拒绝',
      data: transaction
    });
  } catch (error) {
    next(error);
  }
};

// @desc    Delete transaction
// @route   DELETE /api/transactions/:id
// @access  Private/Admin
exports.deleteTransaction = async (req, res, next) => {
  try {
    const transaction = await Transaction.findById(req.params.id);

    if (!transaction) {
      return res.status(404).json({
        success: false,
        message: '交易记录不存在'
      });
    }

    if (transaction.status === TRANSACTION_STATUS.COMPLETED) {
      return res.status(400).json({
        success: false,
        message: '已完成的交易无法删除'
      });
    }

    await transaction.deleteOne();

    res.status(200).json({
      success: true,
      message: '交易记录删除成功',
      data: {}
    });
  } catch (error) {
    next(error);
  }
};

// @desc    Get transaction statistics
// @route   GET /api/transactions/stats
// @access  Private/Admin
exports.getTransactionStats = async (req, res, next) => {
  try {
    const stats = await Transaction.aggregate([
      {
        $group: {
          _id: {
            type: '$type',
            status: '$status'
          },
          total: { $sum: '$amount' },
          count: { $sum: 1 }
        }
      },
      {
        $group: {
          _id: '$_id.type',
          statuses: {
            $push: {
              status: '$_id.status',
              total: '$total',
              count: '$count'
            }
          },
          totalAmount: { $sum: '$total' },
          totalCount: { $sum: '$count' }
        }
      }
    ]);

    // Get daily transactions for the last 30 days
    const thirtyDaysAgo = new Date();
    thirtyDaysAgo.setDate(thirtyDaysAgo.getDate() - 30);

    const dailyStats = await Transaction.aggregate([
      {
        $match: {
          createdAt: { $gte: thirtyDaysAgo }
        }
      },
      {
        $group: {
          _id: {
            $dateToString: { format: '%Y-%m-%d', date: '$createdAt' }
          },
          total: { $sum: '$amount' },
          count: { $sum: 1 }
        }
      },
      { $sort: { _id: 1 } }
    ]);

    res.status(200).json({
      success: true,
      data: {
        byType: stats,
        daily: dailyStats
      }
    });
  } catch (error) {
    next(error);
  }
};
