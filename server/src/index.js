// Providence Admin Backend Server
// Express server for admin configuration management

import express from 'express';
import configsRoutes from './routes/configs.js';
import adminRoutes from './routes/admin.js';

const app = express();
const PORT = process.env.PORT || 3001;

// Middleware
app.use(express.json({ limit: '1mb' }));

// CORS middleware for development
app.use((req, res, next) => {
  res.header('Access-Control-Allow-Origin', '*');
  res.header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
  res.header('Access-Control-Allow-Headers', 'Content-Type, Authorization');

  if (req.method === 'OPTIONS') {
    return res.sendStatus(200);
  }
  next();
});

// Request logging
app.use((req, res, next) => {
  const start = Date.now();
  res.on('finish', () => {
    const duration = Date.now() - start;
    console.log(`${req.method} ${req.path} ${res.statusCode} ${duration}ms`);
  });
  next();
});

// Health check endpoint
app.get('/api/health', (_req, res) => {
  res.json({
    code: 1,
    msg: 'OK',
    data: {
      status: 'healthy',
      timestamp: new Date().toISOString()
    }
  });
});

// Public routes
app.use('/api/configs', configsRoutes);

// Admin routes (protected)
app.use('/api/admin', adminRoutes);

// 404 handler
app.use((_req, res) => {
  res.status(404).json({
    code: 404,
    msg: 'Not found'
  });
});

// Error handler
app.use((err, _req, res, _next) => {
  console.error('Unhandled error:', err);
  res.status(500).json({
    code: 500,
    msg: 'Internal server error'
  });
});

// Start server
app.listen(PORT, () => {
  console.log(`🚀 Providence Admin Server running on port ${PORT}`);
  console.log(`   Health: http://localhost:${PORT}/api/health`);
  console.log(`   Public: http://localhost:${PORT}/api/configs/vip-rules`);
  console.log(`   Admin:  http://localhost:${PORT}/api/admin/configs`);
});

export default app;
