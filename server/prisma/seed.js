// Prisma Seed Script
// Populates VIP rules and team_rewards defaults

import { PrismaClient } from '@prisma/client';

const prisma = new PrismaClient();

// VIP levels configuration based on product requirements
// Data extracted from vip-level.html
const vipRules = [
  {
    level: 1,
    minCumulativeInvest: BigInt(30000),
    extraRatePercent: 0.05,
    inviteLevel1Percent: 0.5,
    inviteLevel2Percent: 0.3,
    signinPoints: 10
  },
  {
    level: 2,
    minCumulativeInvest: BigInt(100000),
    extraRatePercent: 0.10,
    inviteLevel1Percent: 0.6,
    inviteLevel2Percent: 0.35,
    signinPoints: 15
  },
  {
    level: 3,
    minCumulativeInvest: BigInt(250000),
    extraRatePercent: 0.12,
    inviteLevel1Percent: 0.7,
    inviteLevel2Percent: 0.4,
    signinPoints: 20
  },
  {
    level: 4,
    minCumulativeInvest: BigInt(800000),
    extraRatePercent: 0.15,
    inviteLevel1Percent: 0.8,
    inviteLevel2Percent: 0.45,
    signinPoints: 25
  },
  {
    level: 5,
    minCumulativeInvest: BigInt(1500000),
    extraRatePercent: 0.16,
    inviteLevel1Percent: 0.9,
    inviteLevel2Percent: 0.5,
    signinPoints: 30
  },
  {
    level: 6,
    minCumulativeInvest: BigInt(3800000),
    extraRatePercent: 0.18,
    inviteLevel1Percent: 1.0,
    inviteLevel2Percent: 0.55,
    signinPoints: 40
  },
  {
    level: 7,
    minCumulativeInvest: BigInt(8000000),
    extraRatePercent: 0.23,
    inviteLevel1Percent: 1.1,
    inviteLevel2Percent: 0.6,
    signinPoints: 50
  },
  {
    level: 8,
    minCumulativeInvest: BigInt(13000000),
    extraRatePercent: 0.25,
    inviteLevel1Percent: 1.2,
    inviteLevel2Percent: 0.65,
    signinPoints: 60
  }
];

// Team rewards configuration
const teamRewardsConfig = {
  tiers: [
    { memberCount: 5, investAmount: 50000, rewardPoints: 500 },
    { memberCount: 10, investAmount: 150000, rewardPoints: 1500 },
    { memberCount: 20, investAmount: 500000, rewardPoints: 5000 },
    { memberCount: 50, investAmount: 2000000, rewardPoints: 20000 },
    { memberCount: 100, investAmount: 5000000, rewardPoints: 50000 }
  ],
  description: 'Team rewards based on team size and cumulative investment'
};

async function main() {
  console.log('🌱 Starting seed...');

  // Seed VIP rules
  console.log('📊 Seeding VIP rules...');
  for (const rule of vipRules) {
    await prisma.vipRule.upsert({
      where: { level: rule.level },
      update: rule,
      create: rule
    });
    console.log(`  ✅ VIP Level ${rule.level} created/updated`);
  }

  // Seed team_rewards AppConfig
  console.log('📊 Seeding AppConfig...');
  await prisma.appConfig.upsert({
    where: { key: 'team_rewards' },
    update: {
      value: JSON.stringify(teamRewardsConfig)
    },
    create: {
      key: 'team_rewards',
      value: JSON.stringify(teamRewardsConfig)
    }
  });
  console.log('  ✅ team_rewards config created/updated');

  // Add platform settings config
  const platformSettings = {
    appName: 'Providence',
    currency: 'CNY',
    minWithdraw: 100,
    maxWithdraw: 500000,
    withdrawFeePercent: 0.5
  };

  await prisma.appConfig.upsert({
    where: { key: 'platform_settings' },
    update: {
      value: JSON.stringify(platformSettings)
    },
    create: {
      key: 'platform_settings',
      value: JSON.stringify(platformSettings)
    }
  });
  console.log('  ✅ platform_settings config created/updated');

  console.log('✨ Seed completed successfully!');
}

main()
  .catch((e) => {
    console.error('❌ Seed failed:', e);
    process.exit(1);
  })
  .finally(async () => {
    await prisma.$disconnect();
  });
