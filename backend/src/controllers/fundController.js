const Fund = require('../models/Fund');
const Transaction = require('../models/Transaction');
const { FUND_STATUS, TRANSACTION_TYPES, TRANSACTION_STATUS } = require('../config/constants');

// @desc    Get all funds
// @route   GET /api/funds
// @access  Private
exports.getFunds = async (req, res, next) => {
  try {
    const page = parseInt(req.query.page, 10) || 1;
    const limit = parseInt(req.query.limit, 10) || 10;
    const startIndex = (page - 1) * limit;

    const query = {};

    // Filter by status
    if (req.query.status) {
      query.status = req.query.status;
    }

    // Search by name
    if (req.query.search) {
      query.name = { $regex: req.query.search, $options: 'i' };
    }

    // Filter by manager
    if (req.query.manager) {
      query.manager = req.query.manager;
    }

    const total = await Fund.countDocuments(query);
    const funds = await Fund.find(query)
      .populate('manager', 'username email')
      .populate('createdBy', 'username email')
      .skip(startIndex)
      .limit(limit)
      .sort({ createdAt: -1 });

    res.status(200).json({
      success: true,
      data: {
        funds,
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

// @desc    Get single fund
// @route   GET /api/funds/:id
// @access  Private
exports.getFund = async (req, res, next) => {
  try {
    const fund = await Fund.findById(req.params.id)
      .populate('manager', 'username email')
      .populate('createdBy', 'username email')
      .populate('members.user', 'username email');

    if (!fund) {
      return res.status(404).json({
        success: false,
        message: '资金盘不存在'
      });
    }

    res.status(200).json({
      success: true,
      data: fund
    });
  } catch (error) {
    next(error);
  }
};

// @desc    Create fund
// @route   POST /api/funds
// @access  Private/Admin
exports.createFund = async (req, res, next) => {
  try {
    const { name, description, totalAmount, profitRate, manager, startDate, endDate } = req.body;

    const fund = await Fund.create({
      name,
      description,
      totalAmount,
      availableAmount: totalAmount,
      profitRate,
      manager: manager || req.user.id,
      startDate,
      endDate,
      createdBy: req.user.id
    });

    res.status(201).json({
      success: true,
      message: '资金盘创建成功',
      data: fund
    });
  } catch (error) {
    next(error);
  }
};

// @desc    Update fund
// @route   PUT /api/funds/:id
// @access  Private/Admin
exports.updateFund = async (req, res, next) => {
  try {
    const { name, description, profitRate, status, manager, endDate } = req.body;

    let fund = await Fund.findById(req.params.id);

    if (!fund) {
      return res.status(404).json({
        success: false,
        message: '资金盘不存在'
      });
    }

    fund = await Fund.findByIdAndUpdate(
      req.params.id,
      { name, description, profitRate, status, manager, endDate },
      {
        new: true,
        runValidators: true
      }
    ).populate('manager', 'username email');

    res.status(200).json({
      success: true,
      message: '资金盘更新成功',
      data: fund
    });
  } catch (error) {
    next(error);
  }
};

// @desc    Delete fund
// @route   DELETE /api/funds/:id
// @access  Private/Admin
exports.deleteFund = async (req, res, next) => {
  try {
    const fund = await Fund.findById(req.params.id);

    if (!fund) {
      return res.status(404).json({
        success: false,
        message: '资金盘不存在'
      });
    }

    // Check if fund has members
    if (fund.members && fund.members.length > 0) {
      return res.status(400).json({
        success: false,
        message: '资金盘仍有成员，无法删除'
      });
    }

    await fund.deleteOne();

    res.status(200).json({
      success: true,
      message: '资金盘删除成功',
      data: {}
    });
  } catch (error) {
    next(error);
  }
};

// @desc    Add member to fund
// @route   POST /api/funds/:id/members
// @access  Private/Admin
exports.addMember = async (req, res, next) => {
  try {
    const { userId, investedAmount } = req.body;

    const fund = await Fund.findById(req.params.id);

    if (!fund) {
      return res.status(404).json({
        success: false,
        message: '资金盘不存在'
      });
    }

    // Check if user is already a member
    const existingMember = fund.members.find(
      m => m.user.toString() === userId
    );

    if (existingMember) {
      return res.status(400).json({
        success: false,
        message: '用户已是资金盘成员'
      });
    }

    fund.members.push({
      user: userId,
      investedAmount: investedAmount || 0
    });

    await fund.save();

    res.status(200).json({
      success: true,
      message: '成员添加成功',
      data: fund
    });
  } catch (error) {
    next(error);
  }
};

// @desc    Remove member from fund
// @route   DELETE /api/funds/:id/members/:userId
// @access  Private/Admin
exports.removeMember = async (req, res, next) => {
  try {
    const fund = await Fund.findById(req.params.id);

    if (!fund) {
      return res.status(404).json({
        success: false,
        message: '资金盘不存在'
      });
    }

    const memberIndex = fund.members.findIndex(
      m => m.user.toString() === req.params.userId
    );

    if (memberIndex === -1) {
      return res.status(404).json({
        success: false,
        message: '成员不存在'
      });
    }

    fund.members.splice(memberIndex, 1);
    await fund.save();

    res.status(200).json({
      success: true,
      message: '成员移除成功',
      data: fund
    });
  } catch (error) {
    next(error);
  }
};

// @desc    Get fund statistics
// @route   GET /api/funds/:id/stats
// @access  Private
exports.getFundStats = async (req, res, next) => {
  try {
    const fund = await Fund.findById(req.params.id);

    if (!fund) {
      return res.status(404).json({
        success: false,
        message: '资金盘不存在'
      });
    }

    // Get transaction statistics
    const transactions = await Transaction.aggregate([
      { $match: { fund: fund._id } },
      {
        $group: {
          _id: '$type',
          total: { $sum: '$amount' },
          count: { $sum: 1 }
        }
      }
    ]);

    const stats = {
      totalAmount: fund.totalAmount,
      availableAmount: fund.availableAmount,
      lockedAmount: fund.lockedAmount,
      profitRate: fund.profitRate,
      memberCount: fund.members.length,
      transactions: transactions.reduce((acc, t) => {
        acc[t._id] = { total: t.total, count: t.count };
        return acc;
      }, {})
    };

    res.status(200).json({
      success: true,
      data: stats
    });
  } catch (error) {
    next(error);
  }
};
