# Providence Admin Backend Server

Minimal, secure, maintainable backend admin service for managing VIP rules and team-rewards configuration.

## Features

- **Prisma ORM** with SQLite for zero-ops local setup
- **Express.js** REST API under `/api`
- **Admin authentication** via Bearer token
- **Audit logging** for all configuration changes

## Quick Start

### 1. Install Dependencies

```bash
cd server
npm install
```

### 2. Configure Environment

```bash
cp .env.example .env
# Edit .env and set ADMIN_TOKEN to a secure random value
```

### 3. Initialize Database

```bash
npm run setup
# This runs: prisma generate + prisma migrate dev + seed
```

### 4. Start Server

```bash
npm start
# Server runs on http://localhost:3001
```

## API Endpoints

### Public Endpoints (No Auth Required)

| Method | Path | Description |
|--------|------|-------------|
| GET | `/api/health` | Health check |
| GET | `/api/configs/vip-rules` | Get all VIP rules and team_rewards |
| GET | `/api/configs/:key` | Get specific AppConfig by key |

### Admin Endpoints (Requires Bearer Token)

All admin endpoints require `Authorization: Bearer <ADMIN_TOKEN>` header.

| Method | Path | Description |
|--------|------|-------------|
| GET | `/api/admin/configs` | List all configs |
| GET | `/api/admin/configs/:key` | Get specific config |
| PUT | `/api/admin/configs/:key` | Update config (with audit log) |
| GET | `/api/admin/vip-rules` | List all VIP rules |
| PUT | `/api/admin/vip-rules/:level` | Update VIP rule |
| POST | `/api/admin/vip-rules` | Create new VIP rule |

### Example Requests

```bash
# Public - Get VIP rules
curl http://localhost:3001/api/configs/vip-rules

# Admin - Update config
curl -X PUT http://localhost:3001/api/admin/configs/team_rewards \
  -H "Authorization: Bearer your-admin-token" \
  -H "Content-Type: application/json" \
  -d '{"value": {"tiers": [...]}, "reason": "Updated team rewards"}'

# Admin - Update VIP rule
curl -X PUT http://localhost:3001/api/admin/vip-rules/1 \
  -H "Authorization: Bearer your-admin-token" \
  -H "Content-Type: application/json" \
  -d '{"extraRatePercent": 0.06, "reason": "Increased VIP1 bonus"}'
```

## Database Schema

### AppConfig
- `key` (unique string) - Configuration key
- `value` (JSON string) - Configuration value

### VipRule
- `level` (unique int) - VIP level (1-8)
- `minCumulativeInvest` (BigInt) - Minimum investment required
- `extraRatePercent` (Decimal) - Extra rate bonus
- `inviteLevel1Percent` (Decimal) - Level 1 referral commission
- `inviteLevel2Percent` (Decimal) - Level 2 referral commission
- `signinPoints` (Int) - Daily sign-in points

### ConfigAudit
- `key` - Changed configuration key
- `oldValue` - Previous value
- `newValue` - New value
- `changedBy` - Admin identifier
- `reason` - Change reason
- `changedAt` - Timestamp

## Development

```bash
# Run with auto-reload
npm run dev

# Open Prisma Studio (database GUI)
npm run prisma:studio

# Reset database
npm run prisma:reset
```

## Security

- Admin token is required for all `/api/admin/*` endpoints
- Constant-time token comparison to prevent timing attacks
- All configuration changes are audit-logged
- Input validation on all endpoints
