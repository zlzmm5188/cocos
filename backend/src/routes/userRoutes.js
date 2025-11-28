const express = require('express');
const { body } = require('express-validator');
const router = express.Router();

const {
  getUsers,
  getUser,
  createUser,
  updateUser,
  deleteUser,
  toggleUserStatus
} = require('../controllers/userController');

const { protect, adminOnly } = require('../middleware/auth');
const validate = require('../middleware/validate');

// Validation rules
const createUserValidation = [
  body('username')
    .trim()
    .isLength({ min: 3, max: 30 })
    .withMessage('用户名长度为3-30个字符'),
  body('email')
    .trim()
    .isEmail()
    .withMessage('请输入有效的邮箱地址'),
  body('password')
    .isLength({ min: 6 })
    .withMessage('密码至少6个字符')
];

const updateUserValidation = [
  body('username')
    .optional()
    .trim()
    .isLength({ min: 3, max: 30 })
    .withMessage('用户名长度为3-30个字符'),
  body('email')
    .optional()
    .trim()
    .isEmail()
    .withMessage('请输入有效的邮箱地址')
];

// All routes require authentication and admin role
router.use(protect);
router.use(adminOnly);

// Routes
router
  .route('/')
  .get(getUsers)
  .post(createUserValidation, validate, createUser);

router
  .route('/:id')
  .get(getUser)
  .put(updateUserValidation, validate, updateUser)
  .delete(deleteUser);

router.put('/:id/toggle-status', toggleUserStatus);

module.exports = router;
