// Public Config Routes
// Exposes configurable VIP and team-rewards data

import { Router } from 'express';
import { PrismaClient } from '@prisma/client';

const router = Router();
const prisma = new PrismaClient();

/**
 * GET /api/configs/vip-rules
 * Returns all VIP rules and team_rewards config
 * Public endpoint - no auth required
 */
router.get('/vip-rules', async (req, res) => {
  try {
    // Get all VIP rules
    const vipRules = await prisma.vipRule.findMany({
      orderBy: { level: 'asc' }
    });

    // Get team_rewards config
    const teamRewardsConfig = await prisma.appConfig.findUnique({
      where: { key: 'team_rewards' }
    });

    // Format VIP rules for frontend consumption
    const formattedRules = vipRules.map(rule => ({
      level: rule.level,
      minCumulativeInvest: rule.minCumulativeInvest.toString(),
      extraRatePercent: parseFloat(rule.extraRatePercent.toString()),
      inviteLevel1Percent: parseFloat(rule.inviteLevel1Percent.toString()),
      inviteLevel2Percent: parseFloat(rule.inviteLevel2Percent.toString()),
      signinPoints: rule.signinPoints
    }));

    // Parse team_rewards JSON
    let teamRewards = null;
    if (teamRewardsConfig) {
      try {
        teamRewards = JSON.parse(teamRewardsConfig.value);
      } catch (parseError) {
        // Value is not valid JSON, return as-is (could be plain string)
        console.warn('team_rewards value is not valid JSON:', parseError.message);
        teamRewards = teamRewardsConfig.value;
      }
    }

    res.json({
      code: 1,
      msg: 'success',
      data: {
        vipRules: formattedRules,
        teamRewards
      }
    });
  } catch (error) {
    console.error('Error fetching vip-rules:', error);
    res.status(500).json({
      code: 500,
      msg: 'Internal server error'
    });
  }
});

/**
 * GET /api/configs/:key
 * Returns a specific AppConfig by key
 * Public endpoint - no auth required
 */
router.get('/:key', async (req, res) => {
  try {
    const { key } = req.params;

    // Validate key parameter
    if (!key || typeof key !== 'string' || key.length > 100) {
      return res.status(400).json({
        code: 400,
        msg: 'Invalid key parameter'
      });
    }

    const config = await prisma.appConfig.findUnique({
      where: { key }
    });

    if (!config) {
      return res.status(404).json({
        code: 404,
        msg: 'Config not found'
      });
    }

    // Try to parse value as JSON
    let value;
    try {
      value = JSON.parse(config.value);
    } catch (parseError) {
      // Value is not valid JSON, return as-is (could be plain string)
      console.warn(`Config '${key}' value is not valid JSON:`, parseError.message);
      value = config.value;
    }

    res.json({
      code: 1,
      msg: 'success',
      data: {
        key: config.key,
        value,
        updatedAt: config.updatedAt
      }
    });
  } catch (error) {
    console.error('Error fetching config:', error);
    res.status(500).json({
      code: 500,
      msg: 'Internal server error'
    });
  }
});

export default router;
