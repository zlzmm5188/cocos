const jwt = require('jsonwebtoken');
const User = require('../models/User');
const { USER_ROLES } = require('../config/constants');

// Protect routes - verify token
const protect = async (req, res, next) => {
  let token;

  if (
    req.headers.authorization &&
    req.headers.authorization.startsWith('Bearer')
  ) {
    token = req.headers.authorization.split(' ')[1];
  }

  if (!token) {
    return res.status(401).json({
      success: false,
      message: '未授权访问，请先登录'
    });
  }

  try {
    const decoded = jwt.verify(token, process.env.JWT_SECRET);
    req.user = await User.findById(decoded.id);

    if (!req.user) {
      return res.status(401).json({
        success: false,
        message: '用户不存在'
      });
    }

    if (!req.user.isActive) {
      return res.status(401).json({
        success: false,
        message: '账户已被禁用'
      });
    }

    next();
  } catch (err) {
    return res.status(401).json({
      success: false,
      message: '无效的令牌'
    });
  }
};

// Grant access to specific roles
const authorize = (...roles) => {
  return (req, res, next) => {
    if (!roles.includes(req.user.role)) {
      return res.status(403).json({
        success: false,
        message: '无权限访问此资源'
      });
    }
    next();
  };
};

// Admin only middleware
const adminOnly = (req, res, next) => {
  if (req.user.role !== USER_ROLES.ADMIN) {
    return res.status(403).json({
      success: false,
      message: '仅管理员可访问'
    });
  }
  next();
};

module.exports = { protect, authorize, adminOnly };
