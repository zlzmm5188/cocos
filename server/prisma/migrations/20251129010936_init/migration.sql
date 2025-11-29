-- CreateTable
CREATE TABLE "app_config" (
    "id" INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    "key" TEXT NOT NULL,
    "value" TEXT NOT NULL,
    "createdAt" DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "updatedAt" DATETIME NOT NULL
);

-- CreateTable
CREATE TABLE "vip_rule" (
    "id" INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    "level" INTEGER NOT NULL,
    "minCumulativeInvest" BIGINT NOT NULL,
    "extraRatePercent" DECIMAL NOT NULL,
    "inviteLevel1Percent" DECIMAL NOT NULL,
    "inviteLevel2Percent" DECIMAL NOT NULL,
    "signinPoints" INTEGER NOT NULL,
    "createdAt" DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "updatedAt" DATETIME NOT NULL
);

-- CreateTable
CREATE TABLE "config_audit" (
    "id" INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    "key" TEXT NOT NULL,
    "oldValue" TEXT,
    "newValue" TEXT NOT NULL,
    "changedBy" TEXT NOT NULL,
    "reason" TEXT NOT NULL,
    "changedAt" DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- CreateIndex
CREATE UNIQUE INDEX "app_config_key_key" ON "app_config"("key");

-- CreateIndex
CREATE UNIQUE INDEX "vip_rule_level_key" ON "vip_rule"("level");

-- CreateIndex
CREATE INDEX "config_audit_key_idx" ON "config_audit"("key");

-- CreateIndex
CREATE INDEX "config_audit_changedAt_idx" ON "config_audit"("changedAt");
