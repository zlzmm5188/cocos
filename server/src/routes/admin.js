// Admin Routes
// Protected endpoints for managing configs and VIP rules

import { Router } from 'express';
import { PrismaClient } from '@prisma/client';
import { adminAuth } from '../middleware/adminAuth.js';

const router = Router();
const prisma = new PrismaClient();

// Apply admin auth middleware to all routes
router.use(adminAuth);

/**
 * GET /api/admin/configs
 * List all AppConfigs
 */
router.get('/configs', async (req, res) => {
  try {
    const configs = await prisma.appConfig.findMany({
      orderBy: { key: 'asc' }
    });

    // Parse JSON values
    const formattedConfigs = configs.map(config => {
      let value;
      try {
        value = JSON.parse(config.value);
      } catch (parseError) {
        // Value is not valid JSON, return as-is
        console.warn(`Config '${config.key}' value is not valid JSON:`, parseError.message);
        value = config.value;
      }
      return {
        id: config.id,
        key: config.key,
        value,
        createdAt: config.createdAt,
        updatedAt: config.updatedAt
      };
    });

    res.json({
      code: 1,
      msg: 'success',
      data: formattedConfigs
    });
  } catch (error) {
    console.error('Error listing configs:', error);
    res.status(500).json({
      code: 500,
      msg: 'Internal server error'
    });
  }
});

/**
 * GET /api/admin/configs/:key
 * Get specific AppConfig by key
 */
router.get('/configs/:key', async (req, res) => {
  try {
    const { key } = req.params;

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

    let value;
    try {
      value = JSON.parse(config.value);
    } catch (parseError) {
      // Value is not valid JSON, return as-is
      console.warn(`Config '${key}' value is not valid JSON:`, parseError.message);
      value = config.value;
    }

    res.json({
      code: 1,
      msg: 'success',
      data: {
        id: config.id,
        key: config.key,
        value,
        createdAt: config.createdAt,
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

/**
 * PUT /api/admin/configs/:key
 * Update AppConfig by key (creates if not exists)
 * Body: { value: JSON, reason: string }
 * Writes audit log
 */
router.put('/configs/:key', async (req, res) => {
  try {
    const { key } = req.params;
    const { value, reason } = req.body;

    // Validate inputs
    if (!key || typeof key !== 'string' || key.length > 100) {
      return res.status(400).json({
        code: 400,
        msg: 'Invalid key parameter'
      });
    }

    if (value === undefined) {
      return res.status(400).json({
        code: 400,
        msg: 'Value is required'
      });
    }

    if (!reason || typeof reason !== 'string' || reason.length < 1 || reason.length > 500) {
      return res.status(400).json({
        code: 400,
        msg: 'Reason is required (1-500 characters)'
      });
    }

    // Get existing config for audit log
    const existingConfig = await prisma.appConfig.findUnique({
      where: { key }
    });

    const newValueStr = JSON.stringify(value);

    // Update or create config
    const config = await prisma.appConfig.upsert({
      where: { key },
      update: { value: newValueStr },
      create: { key, value: newValueStr }
    });

    // Write audit log
    await prisma.configAudit.create({
      data: {
        key,
        oldValue: existingConfig?.value || null,
        newValue: newValueStr,
        changedBy: req.adminId || 'admin',
        reason
      }
    });

    res.json({
      code: 1,
      msg: 'Config updated successfully',
      data: {
        id: config.id,
        key: config.key,
        value,
        updatedAt: config.updatedAt
      }
    });
  } catch (error) {
    console.error('Error updating config:', error);
    res.status(500).json({
      code: 500,
      msg: 'Internal server error'
    });
  }
});

/**
 * GET /api/admin/vip-rules
 * List all VIP rules
 */
router.get('/vip-rules', async (req, res) => {
  try {
    const rules = await prisma.vipRule.findMany({
      orderBy: { level: 'asc' }
    });

    const formattedRules = rules.map(rule => ({
      id: rule.id,
      level: rule.level,
      minCumulativeInvest: rule.minCumulativeInvest.toString(),
      extraRatePercent: parseFloat(rule.extraRatePercent.toString()),
      inviteLevel1Percent: parseFloat(rule.inviteLevel1Percent.toString()),
      inviteLevel2Percent: parseFloat(rule.inviteLevel2Percent.toString()),
      signinPoints: rule.signinPoints,
      createdAt: rule.createdAt,
      updatedAt: rule.updatedAt
    }));

    res.json({
      code: 1,
      msg: 'success',
      data: formattedRules
    });
  } catch (error) {
    console.error('Error listing vip rules:', error);
    res.status(500).json({
      code: 500,
      msg: 'Internal server error'
    });
  }
});

/**
 * PUT /api/admin/vip-rules/:level
 * Update VIP rule by level
 */
router.put('/vip-rules/:level', async (req, res) => {
  try {
    const level = parseInt(req.params.level, 10);
    const {
      minCumulativeInvest,
      extraRatePercent,
      inviteLevel1Percent,
      inviteLevel2Percent,
      signinPoints,
      reason
    } = req.body;

    // Validate level
    if (isNaN(level) || level < 1 || level > 100) {
      return res.status(400).json({
        code: 400,
        msg: 'Invalid level parameter (must be 1-100)'
      });
    }

    // Check if rule exists
    const existingRule = await prisma.vipRule.findUnique({
      where: { level }
    });

    if (!existingRule) {
      return res.status(404).json({
        code: 404,
        msg: 'VIP rule not found'
      });
    }

    // Build update data (only include provided fields) with validation
    const updateData = {};
    if (minCumulativeInvest !== undefined) {
      const investValue = String(minCumulativeInvest).replace(/[^0-9-]/g, '');
      if (!investValue || isNaN(Number(investValue))) {
        return res.status(400).json({
          code: 400,
          msg: 'minCumulativeInvest must be a valid numeric value'
        });
      }
      updateData.minCumulativeInvest = BigInt(investValue);
    }
    if (extraRatePercent !== undefined) {
      const rateValue = parseFloat(extraRatePercent);
      if (isNaN(rateValue)) {
        return res.status(400).json({
          code: 400,
          msg: 'extraRatePercent must be a valid number'
        });
      }
      updateData.extraRatePercent = rateValue;
    }
    if (inviteLevel1Percent !== undefined) {
      const l1Value = parseFloat(inviteLevel1Percent);
      if (isNaN(l1Value)) {
        return res.status(400).json({
          code: 400,
          msg: 'inviteLevel1Percent must be a valid number'
        });
      }
      updateData.inviteLevel1Percent = l1Value;
    }
    if (inviteLevel2Percent !== undefined) {
      const l2Value = parseFloat(inviteLevel2Percent);
      if (isNaN(l2Value)) {
        return res.status(400).json({
          code: 400,
          msg: 'inviteLevel2Percent must be a valid number'
        });
      }
      updateData.inviteLevel2Percent = l2Value;
    }
    if (signinPoints !== undefined) {
      const pointsValue = parseInt(signinPoints, 10);
      if (isNaN(pointsValue)) {
        return res.status(400).json({
          code: 400,
          msg: 'signinPoints must be a valid integer'
        });
      }
      updateData.signinPoints = pointsValue;
    }

    if (Object.keys(updateData).length === 0) {
      return res.status(400).json({
        code: 400,
        msg: 'No valid fields to update'
      });
    }

    // Update the rule
    const rule = await prisma.vipRule.update({
      where: { level },
      data: updateData
    });

    // Write audit log for VIP rule change
    await prisma.configAudit.create({
      data: {
        key: `vip_rule_level_${level}`,
        oldValue: JSON.stringify({
          minCumulativeInvest: existingRule.minCumulativeInvest.toString(),
          extraRatePercent: existingRule.extraRatePercent.toString(),
          inviteLevel1Percent: existingRule.inviteLevel1Percent.toString(),
          inviteLevel2Percent: existingRule.inviteLevel2Percent.toString(),
          signinPoints: existingRule.signinPoints
        }),
        newValue: JSON.stringify({
          minCumulativeInvest: rule.minCumulativeInvest.toString(),
          extraRatePercent: rule.extraRatePercent.toString(),
          inviteLevel1Percent: rule.inviteLevel1Percent.toString(),
          inviteLevel2Percent: rule.inviteLevel2Percent.toString(),
          signinPoints: rule.signinPoints
        }),
        changedBy: req.adminId || 'admin',
        reason: reason || 'VIP rule updated'
      }
    });

    res.json({
      code: 1,
      msg: 'VIP rule updated successfully',
      data: {
        id: rule.id,
        level: rule.level,
        minCumulativeInvest: rule.minCumulativeInvest.toString(),
        extraRatePercent: parseFloat(rule.extraRatePercent.toString()),
        inviteLevel1Percent: parseFloat(rule.inviteLevel1Percent.toString()),
        inviteLevel2Percent: parseFloat(rule.inviteLevel2Percent.toString()),
        signinPoints: rule.signinPoints,
        updatedAt: rule.updatedAt
      }
    });
  } catch (error) {
    console.error('Error updating vip rule:', error);
    res.status(500).json({
      code: 500,
      msg: 'Internal server error'
    });
  }
});

/**
 * POST /api/admin/vip-rules
 * Create a new VIP rule
 */
router.post('/vip-rules', async (req, res) => {
  try {
    const {
      level,
      minCumulativeInvest,
      extraRatePercent,
      inviteLevel1Percent,
      inviteLevel2Percent,
      signinPoints,
      reason
    } = req.body;

    // Validate required fields
    if (level === undefined || level === null) {
      return res.status(400).json({
        code: 400,
        msg: 'Level is required'
      });
    }

    const levelInt = parseInt(level, 10);
    if (isNaN(levelInt) || levelInt < 1 || levelInt > 100) {
      return res.status(400).json({
        code: 400,
        msg: 'Invalid level (must be 1-100)'
      });
    }

    if (minCumulativeInvest === undefined) {
      return res.status(400).json({
        code: 400,
        msg: 'minCumulativeInvest is required'
      });
    }

    if (extraRatePercent === undefined) {
      return res.status(400).json({
        code: 400,
        msg: 'extraRatePercent is required'
      });
    }

    if (inviteLevel1Percent === undefined) {
      return res.status(400).json({
        code: 400,
        msg: 'inviteLevel1Percent is required'
      });
    }

    if (inviteLevel2Percent === undefined) {
      return res.status(400).json({
        code: 400,
        msg: 'inviteLevel2Percent is required'
      });
    }

    if (signinPoints === undefined) {
      return res.status(400).json({
        code: 400,
        msg: 'signinPoints is required'
      });
    }

    // Validate numeric values
    const investValue = String(minCumulativeInvest).replace(/[^0-9-]/g, '');
    if (!investValue || isNaN(Number(investValue))) {
      return res.status(400).json({
        code: 400,
        msg: 'minCumulativeInvest must be a valid numeric value'
      });
    }

    const extraRateValue = parseFloat(extraRatePercent);
    if (isNaN(extraRateValue)) {
      return res.status(400).json({
        code: 400,
        msg: 'extraRatePercent must be a valid number'
      });
    }

    const l1Value = parseFloat(inviteLevel1Percent);
    if (isNaN(l1Value)) {
      return res.status(400).json({
        code: 400,
        msg: 'inviteLevel1Percent must be a valid number'
      });
    }

    const l2Value = parseFloat(inviteLevel2Percent);
    if (isNaN(l2Value)) {
      return res.status(400).json({
        code: 400,
        msg: 'inviteLevel2Percent must be a valid number'
      });
    }

    const pointsValue = parseInt(signinPoints, 10);
    if (isNaN(pointsValue)) {
      return res.status(400).json({
        code: 400,
        msg: 'signinPoints must be a valid integer'
      });
    }

    // Check if level already exists
    const existingRule = await prisma.vipRule.findUnique({
      where: { level: levelInt }
    });

    if (existingRule) {
      return res.status(409).json({
        code: 409,
        msg: 'VIP rule with this level already exists'
      });
    }

    // Create the rule
    const rule = await prisma.vipRule.create({
      data: {
        level: levelInt,
        minCumulativeInvest: BigInt(investValue),
        extraRatePercent: extraRateValue,
        inviteLevel1Percent: l1Value,
        inviteLevel2Percent: l2Value,
        signinPoints: pointsValue
      }
    });

    // Write audit log
    await prisma.configAudit.create({
      data: {
        key: `vip_rule_level_${levelInt}`,
        oldValue: null,
        newValue: JSON.stringify({
          level: rule.level,
          minCumulativeInvest: rule.minCumulativeInvest.toString(),
          extraRatePercent: rule.extraRatePercent.toString(),
          inviteLevel1Percent: rule.inviteLevel1Percent.toString(),
          inviteLevel2Percent: rule.inviteLevel2Percent.toString(),
          signinPoints: rule.signinPoints
        }),
        changedBy: req.adminId || 'admin',
        reason: reason || 'VIP rule created'
      }
    });

    res.status(201).json({
      code: 1,
      msg: 'VIP rule created successfully',
      data: {
        id: rule.id,
        level: rule.level,
        minCumulativeInvest: rule.minCumulativeInvest.toString(),
        extraRatePercent: parseFloat(rule.extraRatePercent.toString()),
        inviteLevel1Percent: parseFloat(rule.inviteLevel1Percent.toString()),
        inviteLevel2Percent: parseFloat(rule.inviteLevel2Percent.toString()),
        signinPoints: rule.signinPoints,
        createdAt: rule.createdAt
      }
    });
  } catch (error) {
    console.error('Error creating vip rule:', error);
    res.status(500).json({
      code: 500,
      msg: 'Internal server error'
    });
  }
});

export default router;
