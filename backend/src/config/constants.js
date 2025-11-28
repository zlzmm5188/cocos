// User roles
const USER_ROLES = {
  ADMIN: 'admin',
  USER: 'user',
  MANAGER: 'manager'
};

// Transaction types
const TRANSACTION_TYPES = {
  DEPOSIT: 'deposit',
  WITHDRAW: 'withdraw',
  TRANSFER: 'transfer',
  PROFIT: 'profit',
  LOSS: 'loss'
};

// Transaction status
const TRANSACTION_STATUS = {
  PENDING: 'pending',
  COMPLETED: 'completed',
  FAILED: 'failed',
  CANCELLED: 'cancelled'
};

// Fund status
const FUND_STATUS = {
  ACTIVE: 'active',
  INACTIVE: 'inactive',
  CLOSED: 'closed'
};

module.exports = {
  USER_ROLES,
  TRANSACTION_TYPES,
  TRANSACTION_STATUS,
  FUND_STATUS
};
