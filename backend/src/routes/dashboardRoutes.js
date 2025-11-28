const express = require('express');
const router = express.Router();

const {
  getDashboardStats,
  getChartData
} = require('../controllers/dashboardController');

const { protect, authorize } = require('../middleware/auth');
const { USER_ROLES } = require('../config/constants');

// All routes require authentication and admin/manager role
router.use(protect);
router.use(authorize(USER_ROLES.ADMIN, USER_ROLES.MANAGER));

// Routes
router.get('/stats', getDashboardStats);
router.get('/charts', getChartData);

module.exports = router;
