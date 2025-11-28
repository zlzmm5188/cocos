const mongoose = require('mongoose');
const { FUND_STATUS } = require('../config/constants');

const fundSchema = new mongoose.Schema({
  name: {
    type: String,
    required: [true, '请输入资金盘名称'],
    trim: true,
    maxlength: [100, '名称最多100个字符']
  },
  description: {
    type: String,
    trim: true,
    maxlength: [500, '描述最多500个字符']
  },
  totalAmount: {
    type: Number,
    required: [true, '请输入总金额'],
    min: [0, '金额不能为负数'],
    default: 0
  },
  availableAmount: {
    type: Number,
    min: [0, '可用金额不能为负数'],
    default: 0
  },
  lockedAmount: {
    type: Number,
    min: [0, '锁定金额不能为负数'],
    default: 0
  },
  profitRate: {
    type: Number,
    min: [0, '收益率不能为负数'],
    default: 0
  },
  status: {
    type: String,
    enum: Object.values(FUND_STATUS),
    default: FUND_STATUS.ACTIVE
  },
  manager: {
    type: mongoose.Schema.Types.ObjectId,
    ref: 'User',
    required: [true, '请指定资金盘管理员']
  },
  members: [{
    user: {
      type: mongoose.Schema.Types.ObjectId,
      ref: 'User'
    },
    investedAmount: {
      type: Number,
      default: 0
    },
    joinedAt: {
      type: Date,
      default: Date.now
    }
  }],
  startDate: {
    type: Date,
    default: Date.now
  },
  endDate: {
    type: Date
  },
  createdBy: {
    type: mongoose.Schema.Types.ObjectId,
    ref: 'User',
    required: true
  }
}, {
  timestamps: true
});

// Virtual for calculating total members
fundSchema.virtual('memberCount').get(function() {
  return this.members.length;
});

// Ensure virtuals are included in JSON output
fundSchema.set('toJSON', { virtuals: true });
fundSchema.set('toObject', { virtuals: true });

module.exports = mongoose.model('Fund', fundSchema);
