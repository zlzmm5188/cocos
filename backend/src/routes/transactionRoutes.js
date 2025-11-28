const express = require('express');
const { body } = require('express-validator');
const router = express.Router();

const {
  getTransactions,
  getTransaction,
  createTransaction,
  processTransaction,
  deleteTransaction,
  getTransactionStats
} = require('../controllers/transactionController');

const { protect, adminOnly, authorize } = require('../middleware/auth');
const validate = require('../middleware/validate');
const { USER_ROLES, TRANSACTION_TYPES, TRANSACTION_STATUS } = require('../config/constants');

// Validation rules
const createTransactionValidation = [
  body('type')
    .isIn(Object.values(TRANSACTION_TYPES))
    .withMessage('无效的交易类型'),
  body('amount')
    .isNumeric()
    .withMessage('请输入有效的金额')
    .custom(value => value > 0)
    .withMessage('金额必须大于0'),
  body('fund')
    .notEmpty()
    .withMessage('请指定资金盘')
];

const processTransactionValidation = [
  body('status')
    .isIn(Object.values(TRANSACTION_STATUS))
    .withMessage('无效的状态')
];

// All routes require authentication
router.use(protect);

// Routes
router.get('/stats', authorize(USER_ROLES.ADMIN, USER_ROLES.MANAGER), getTransactionStats);

router
  .route('/')
  .get(getTransactions)
  .post(createTransactionValidation, validate, createTransaction);

router
  .route('/:id')
  .get(getTransaction)
  .delete(adminOnly, deleteTransaction);

router.put(
  '/:id/process',
  authorize(USER_ROLES.ADMIN, USER_ROLES.MANAGER),
  processTransactionValidation,
  validate,
  processTransaction
);

module.exports = router;
