const mongoose = require('mongoose');
const bcrypt = require('bcryptjs');
require('dotenv').config();

const User = require('../models/User');
const Fund = require('../models/Fund');
const Transaction = require('../models/Transaction');
const { USER_ROLES, FUND_STATUS, TRANSACTION_TYPES, TRANSACTION_STATUS } = require('../config/constants');

// Connect to DB
mongoose.connect(process.env.MONGODB_URI || 'mongodb://localhost:27017/fund_management');

// Sample data
const users = [
  {
    username: 'admin',
    email: 'admin@example.com',
    password: 'admin123',
    role: USER_ROLES.ADMIN,
    phone: '13800138000'
  },
  {
    username: 'manager1',
    email: 'manager1@example.com',
    password: 'manager123',
    role: USER_ROLES.MANAGER,
    phone: '13800138001'
  },
  {
    username: 'user1',
    email: 'user1@example.com',
    password: 'user123',
    role: USER_ROLES.USER,
    phone: '13800138002'
  },
  {
    username: 'user2',
    email: 'user2@example.com',
    password: 'user123',
    role: USER_ROLES.USER,
    phone: '13800138003'
  }
];

// Import data
const importData = async () => {
  try {
    // Clear existing data
    await User.deleteMany();
    await Fund.deleteMany();
    await Transaction.deleteMany();

    console.log('数据已清除...');

    // Create users
    const createdUsers = await User.create(users);
    console.log('用户创建成功');

    const admin = createdUsers[0];
    const manager = createdUsers[1];
    const user1 = createdUsers[2];
    const user2 = createdUsers[3];

    // Create funds
    const funds = [
      {
        name: '稳健型资金池A',
        description: '低风险稳健收益型资金池',
        totalAmount: 1000000,
        availableAmount: 800000,
        lockedAmount: 200000,
        profitRate: 5.5,
        status: FUND_STATUS.ACTIVE,
        manager: manager._id,
        members: [
          { user: user1._id, investedAmount: 50000 },
          { user: user2._id, investedAmount: 30000 }
        ],
        createdBy: admin._id
      },
      {
        name: '成长型资金池B',
        description: '中等风险成长收益型资金池',
        totalAmount: 500000,
        availableAmount: 450000,
        lockedAmount: 50000,
        profitRate: 8.0,
        status: FUND_STATUS.ACTIVE,
        manager: manager._id,
        members: [
          { user: user1._id, investedAmount: 100000 }
        ],
        createdBy: admin._id
      },
      {
        name: '高收益资金池C',
        description: '高风险高收益型资金池',
        totalAmount: 200000,
        availableAmount: 200000,
        lockedAmount: 0,
        profitRate: 12.0,
        status: FUND_STATUS.INACTIVE,
        manager: admin._id,
        createdBy: admin._id
      }
    ];

    const createdFunds = await Fund.create(funds);
    console.log('资金盘创建成功');

    // Create sample transactions
    const transactions = [
      {
        type: TRANSACTION_TYPES.DEPOSIT,
        amount: 50000,
        status: TRANSACTION_STATUS.COMPLETED,
        fund: createdFunds[0]._id,
        user: user1._id,
        description: '首次入金',
        balanceBefore: 0,
        balanceAfter: 50000,
        processedAt: new Date(),
        processedBy: admin._id
      },
      {
        type: TRANSACTION_TYPES.DEPOSIT,
        amount: 30000,
        status: TRANSACTION_STATUS.COMPLETED,
        fund: createdFunds[0]._id,
        user: user2._id,
        description: '首次入金',
        balanceBefore: 50000,
        balanceAfter: 80000,
        processedAt: new Date(),
        processedBy: admin._id
      },
      {
        type: TRANSACTION_TYPES.PROFIT,
        amount: 2500,
        status: TRANSACTION_STATUS.COMPLETED,
        fund: createdFunds[0]._id,
        user: user1._id,
        description: '月度收益',
        balanceBefore: 80000,
        balanceAfter: 82500,
        processedAt: new Date(),
        processedBy: manager._id
      },
      {
        type: TRANSACTION_TYPES.WITHDRAW,
        amount: 10000,
        status: TRANSACTION_STATUS.PENDING,
        fund: createdFunds[0]._id,
        user: user1._id,
        description: '提现申请'
      },
      {
        type: TRANSACTION_TYPES.DEPOSIT,
        amount: 100000,
        status: TRANSACTION_STATUS.COMPLETED,
        fund: createdFunds[1]._id,
        user: user1._id,
        description: '追加投资',
        balanceBefore: 0,
        balanceAfter: 100000,
        processedAt: new Date(),
        processedBy: admin._id
      }
    ];

    await Transaction.create(transactions);
    console.log('交易记录创建成功');

    console.log('数据导入完成!');
    console.log('管理员账户: admin@example.com / admin123');
    console.log('经理账户: manager1@example.com / manager123');
    console.log('用户账户: user1@example.com / user123');
    process.exit();
  } catch (error) {
    console.error(`错误: ${error.message}`);
    process.exit(1);
  }
};

// Delete data
const deleteData = async () => {
  try {
    await User.deleteMany();
    await Fund.deleteMany();
    await Transaction.deleteMany();

    console.log('数据已删除!');
    process.exit();
  } catch (error) {
    console.error(`错误: ${error.message}`);
    process.exit(1);
  }
};

// Run based on argument
if (process.argv[2] === '-d') {
  deleteData();
} else {
  importData();
}
