// Admin Authentication Middleware
// Validates Bearer token against ADMIN_TOKEN environment variable

/**
 * Middleware to authenticate admin requests
 * Expects Authorization header: Bearer <ADMIN_TOKEN>
 */
export function adminAuth(req, res, next) {
  const adminToken = process.env.ADMIN_TOKEN;

  if (!adminToken) {
    console.error('ADMIN_TOKEN environment variable is not set');
    return res.status(500).json({
      code: 500,
      msg: 'Server configuration error'
    });
  }

  const authHeader = req.headers.authorization;

  if (!authHeader) {
    return res.status(401).json({
      code: 401,
      msg: 'Authorization header is required'
    });
  }

  // Validate Bearer token format
  const parts = authHeader.split(' ');
  if (parts.length !== 2 || parts[0] !== 'Bearer') {
    return res.status(401).json({
      code: 401,
      msg: 'Invalid authorization format. Use: Bearer <token>'
    });
  }

  const token = parts[1];

  // Constant-time comparison to prevent timing attacks
  if (!safeCompare(token, adminToken)) {
    return res.status(403).json({
      code: 403,
      msg: 'Invalid admin token'
    });
  }

  // Token is valid, proceed to next middleware/route
  req.adminId = 'admin'; // Could be expanded for multi-admin support
  next();
}

/**
 * Constant-time string comparison to prevent timing attacks
 */
function safeCompare(a, b) {
  if (typeof a !== 'string' || typeof b !== 'string') {
    return false;
  }

  if (a.length !== b.length) {
    return false;
  }

  let result = 0;
  for (let i = 0; i < a.length; i++) {
    result |= a.charCodeAt(i) ^ b.charCodeAt(i);
  }

  return result === 0;
}
