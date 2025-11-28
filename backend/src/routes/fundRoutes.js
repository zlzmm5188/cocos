const express = require('express');
const { body } = require('express-validator');
const router = express.Router();

const {
  getFunds,
  getFund,
  createFund,
  updateFund,
  deleteFund,
  addMember,
  removeMember,
  getFundStats
} = require('../controllers/fundController');

const { protect, adminOnly, authorize } = require('../middleware/auth');
const validate = require('../middleware/validate');
const { USER_ROLES } = require('../config/constants');

// Validation rules
const createFundValidation = [
  body('name')
    .trim()
    .notEmpty()
    .withMessage('请输入资金盘名称')
    .isLength({ max: 100 })
    .withMessage('名称最多100个字符'),
  body('totalAmount')
    .isNumeric()
    .withMessage('请输入有效的金额')
    .custom(value => value >= 0)
    .withMessage('金额不能为负数')
];

const updateFundValidation = [
  body('name')
    .optional()
    .trim()
    .isLength({ max: 100 })
    .withMessage('名称最多100个字符'),
  body('profitRate')
    .optional()
    .isNumeric()
    .withMessage('收益率必须是数字')
];

const addMemberValidation = [
  body('userId')
    .notEmpty()
    .withMessage('请指定用户'),
  body('investedAmount')
    .optional()
    .isNumeric()
    .withMessage('投资金额必须是数字')
];

// All routes require authentication
router.use(protect);

// Routes
router
  .route('/')
  .get(getFunds)
  .post(authorize(USER_ROLES.ADMIN, USER_ROLES.MANAGER), createFundValidation, validate, createFund);

router
  .route('/:id')
  .get(getFund)
  .put(authorize(USER_ROLES.ADMIN, USER_ROLES.MANAGER), updateFundValidation, validate, updateFund)
  .delete(adminOnly, deleteFund);

router.get('/:id/stats', getFundStats);

router
  .route('/:id/members')
  .post(authorize(USER_ROLES.ADMIN, USER_ROLES.MANAGER), addMemberValidation, validate, addMember);

router.delete(
  '/:id/members/:userId',
  authorize(USER_ROLES.ADMIN, USER_ROLES.MANAGER),
  removeMember
);

module.exports = router;
