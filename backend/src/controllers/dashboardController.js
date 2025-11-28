const Fund = require('../models/Fund');
const Transaction = require('../models/Transaction');
const User = require('../models/User');
const { TRANSACTION_STATUS, FUND_STATUS } = require('../config/constants');

// @desc    Get dashboard statistics
// @route   GET /api/dashboard/stats
// @access  Private/Admin
exports.getDashboardStats = async (req, res, next) => {
  try {
    // Get user count
    const userCount = await User.countDocuments();
    const activeUserCount = await User.countDocuments({ isActive: true });

    // Get fund statistics
    const fundCount = await Fund.countDocuments();
    const activeFundCount = await Fund.countDocuments({ status: FUND_STATUS.ACTIVE });

    const fundAggregation = await Fund.aggregate([
      {
        $group: {
          _id: null,
          totalAmount: { $sum: '$totalAmount' },
          totalAvailable: { $sum: '$availableAmount' },
          totalLocked: { $sum: '$lockedAmount' }
        }
      }
    ]);

    const fundStats = fundAggregation[0] || {
      totalAmount: 0,
      totalAvailable: 0,
      totalLocked: 0
    };

    // Get transaction statistics
    const transactionCount = await Transaction.countDocuments();
    const pendingTransactionCount = await Transaction.countDocuments({
      status: TRANSACTION_STATUS.PENDING
    });

    const transactionAggregation = await Transaction.aggregate([
      {
        $match: { status: TRANSACTION_STATUS.COMPLETED }
      },
      {
        $group: {
          _id: '$type',
          total: { $sum: '$amount' },
          count: { $sum: 1 }
        }
      }
    ]);

    const transactionStats = transactionAggregation.reduce((acc, t) => {
      acc[t._id] = { total: t.total, count: t.count };
      return acc;
    }, {});

    // Get recent transactions
    const recentTransactions = await Transaction.find()
      .populate('fund', 'name')
      .populate('user', 'username')
      .sort({ createdAt: -1 })
      .limit(10);

    // Get top funds by total amount
    const topFunds = await Fund.find({ status: FUND_STATUS.ACTIVE })
      .sort({ totalAmount: -1 })
      .limit(5)
      .populate('manager', 'username');

    res.status(200).json({
      success: true,
      data: {
        users: {
          total: userCount,
          active: activeUserCount
        },
        funds: {
          total: fundCount,
          active: activeFundCount,
          ...fundStats
        },
        transactions: {
          total: transactionCount,
          pending: pendingTransactionCount,
          byType: transactionStats
        },
        recentTransactions,
        topFunds
      }
    });
  } catch (error) {
    next(error);
  }
};

// @desc    Get chart data for dashboard
// @route   GET /api/dashboard/charts
// @access  Private/Admin
exports.getChartData = async (req, res, next) => {
  try {
    const days = parseInt(req.query.days, 10) || 30;
    const startDate = new Date();
    startDate.setDate(startDate.getDate() - days);

    // Transaction trend
    const transactionTrend = await Transaction.aggregate([
      {
        $match: {
          createdAt: { $gte: startDate },
          status: TRANSACTION_STATUS.COMPLETED
        }
      },
      {
        $group: {
          _id: {
            date: { $dateToString: { format: '%Y-%m-%d', date: '$createdAt' } },
            type: '$type'
          },
          amount: { $sum: '$amount' },
          count: { $sum: 1 }
        }
      },
      {
        $group: {
          _id: '$_id.date',
          transactions: {
            $push: {
              type: '$_id.type',
              amount: '$amount',
              count: '$count'
            }
          },
          totalAmount: { $sum: '$amount' },
          totalCount: { $sum: '$count' }
        }
      },
      { $sort: { _id: 1 } }
    ]);

    // User registration trend
    const userTrend = await User.aggregate([
      {
        $match: { createdAt: { $gte: startDate } }
      },
      {
        $group: {
          _id: { $dateToString: { format: '%Y-%m-%d', date: '$createdAt' } },
          count: { $sum: 1 }
        }
      },
      { $sort: { _id: 1 } }
    ]);

    // Fund distribution by status
    const fundDistribution = await Fund.aggregate([
      {
        $group: {
          _id: '$status',
          count: { $sum: 1 },
          totalAmount: { $sum: '$totalAmount' }
        }
      }
    ]);

    res.status(200).json({
      success: true,
      data: {
        transactionTrend,
        userTrend,
        fundDistribution
      }
    });
  } catch (error) {
    next(error);
  }
};
