<?php
/**
 * KYC控制器 - 实名认证
 */

class KycController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * 获取KYC状态
     */
    public function getStatus() {
        $user = Auth::require();

        if ($this->db && $this->db->isConnected()) {
            $userInfo = $this->db->fetch("SELECT kyc_status, real_name, id_card FROM users WHERE id = ?", [$user['user_id']]);
            
            $kycRecord = $this->db->fetch(
                "SELECT * FROM kyc_records WHERE user_id = ? ORDER BY created_at DESC LIMIT 1",
                [$user['user_id']]
            );

            success([
                'status' => (int)($userInfo['kyc_status'] ?? 0),
                'status_text' => $this->getStatusText($userInfo['kyc_status'] ?? 0),
                'real_name' => $userInfo['real_name'] ? substr($userInfo['real_name'], 0, 1) . '**' : '',
                'id_card' => $userInfo['id_card'] ? substr($userInfo['id_card'], 0, 4) . '**********' . substr($userInfo['id_card'], -4) : '',
                'reject_reason' => $kycRecord['reject_reason'] ?? '',
                'submitted_at' => $kycRecord['created_at'] ?? ''
            ]);
        } else {
            success([
                'status' => 0,
                'status_text' => '未认证',
                'real_name' => '',
                'id_card' => '',
                'reject_reason' => '',
                'submitted_at' => ''
            ]);
        }
    }

    /**
     * 提交KYC认证
     */
    public function submit() {
        $user = Auth::require();
        $data = getJsonInput();

        $realName = trim($data['real_name'] ?? $data['realName'] ?? '');
        $idCard = trim($data['id_card'] ?? $data['idCard'] ?? '');
        $idFrontImage = $data['id_front_image'] ?? $data['idFrontImage'] ?? '';
        $idBackImage = $data['id_back_image'] ?? $data['idBackImage'] ?? '';
        $faceImage = $data['face_image'] ?? $data['faceImage'] ?? '';

        if (empty($realName)) {
            error('请输入真实姓名');
        }

        if (empty($idCard)) {
            error('请输入身份证号');
        }

        if (!isValidIdCard($idCard)) {
            error('身份证号格式不正确');
        }

        if ($this->db && $this->db->isConnected()) {
            // 检查是否已认证
            $userInfo = $this->db->fetch("SELECT kyc_status FROM users WHERE id = ?", [$user['user_id']]);
            if ($userInfo['kyc_status'] == 2) {
                error('您已完成实名认证');
            }

            $this->db->beginTransaction();
            try {
                // 更新用户信息
                $this->db->update('users', [
                    'real_name' => $realName,
                    'id_card' => $idCard,
                    'kyc_status' => 1 // 审核中
                ], 'id = ?', [$user['user_id']]);

                // 创建KYC记录
                $this->db->insert('kyc_records', [
                    'user_id' => $user['user_id'],
                    'real_name' => $realName,
                    'id_card' => $idCard,
                    'id_front_image' => $idFrontImage,
                    'id_back_image' => $idBackImage,
                    'face_image' => $faceImage,
                    'status' => 1,
                    'created_at' => date('Y-m-d H:i:s')
                ]);

                $this->db->commit();

                success(null, '提交成功，请等待审核');
            } catch (Exception $e) {
                $this->db->rollBack();
                error('提交失败');
            }
        } else {
            success(null, '提交成功，请等待审核');
        }
    }

    /**
     * 简化版KYC提交（无需图片）
     */
    public function submitSimple() {
        $user = Auth::require();
        $data = getJsonInput();

        $realName = trim($data['real_name'] ?? $data['realName'] ?? '');
        $idCard = trim($data['id_card'] ?? $data['idCard'] ?? '');

        if (empty($realName)) {
            error('请输入真实姓名');
        }

        if (empty($idCard)) {
            error('请输入身份证号');
        }

        if ($this->db && $this->db->isConnected()) {
            $userInfo = $this->db->fetch("SELECT kyc_status FROM users WHERE id = ?", [$user['user_id']]);
            if ($userInfo['kyc_status'] == 2) {
                error('您已完成实名认证');
            }

            // 简化版直接通过
            $this->db->update('users', [
                'real_name' => $realName,
                'id_card' => $idCard,
                'kyc_status' => 2 // 已认证
            ], 'id = ?', [$user['user_id']]);

            success(null, '认证成功');
        } else {
            success(null, '认证成功');
        }
    }

    private function getStatusText($status) {
        $texts = [
            0 => '未认证',
            1 => '审核中',
            2 => '已认证',
            3 => '认证失败'
        ];
        return $texts[$status] ?? '未知';
    }
}
