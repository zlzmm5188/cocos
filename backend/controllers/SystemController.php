<?php
/**
 * 系统控制器 - 公告、活动等
 */

class SystemController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * 获取公告列表
     */
    public function getAnnouncements() {
        $pagination = getPagination();

        if ($this->db && $this->db->isConnected()) {
            $total = $this->db->count('announcements', 'status = 1');

            // 使用参数绑定防止SQL注入
            $offset = intval($pagination['offset']);
            $pageSize = intval($pagination['pageSize']);
            $sql = "SELECT * FROM announcements WHERE status = 1 ORDER BY is_top DESC, created_at DESC LIMIT ?, ?";
            $announcements = $this->db->fetchAll($sql, [$offset, $pageSize]);

            $items = array_map(function($a) {
                return [
                    'id' => $a['id'],
                    'title' => $a['title'],
                    'content' => $a['content'],
                    'is_top' => (int)$a['is_top'],
                    'created_at' => $a['created_at']
                ];
            }, $announcements);

            success(paginateResponse($items, $total, $pagination['page'], $pagination['pageSize']));
        } else {
            $mockAnnouncements = [
                [
                    'id' => 1,
                    'title' => '系统升级公告',
                    'content' => '尊敬的用户，我们将于2024年12月1日进行系统升级，届时服务可能会短暂中断，敬请谅解。',
                    'is_top' => 1,
                    'created_at' => '2024-11-28 10:00:00'
                ],
                [
                    'id' => 2,
                    'title' => '新年活动预告',
                    'content' => '2025新年活动即将开启，敬请期待！',
                    'is_top' => 0,
                    'created_at' => '2024-11-25 14:00:00'
                ],
                [
                    'id' => 3,
                    'title' => 'VIP等级权益更新',
                    'content' => '我们更新了VIP等级权益，详情请查看VIP等级页面。',
                    'is_top' => 0,
                    'created_at' => '2024-11-20 09:00:00'
                ]
            ];
            success(paginateResponse($mockAnnouncements, count($mockAnnouncements), $pagination['page'], $pagination['pageSize']));
        }
    }

    /**
     * 获取活动弹窗
     */
    public function getActivityPopup() {
        if ($this->db && $this->db->isConnected()) {
            $activity = $this->db->fetch(
                "SELECT * FROM activities WHERE status = 1 AND start_time <= NOW() AND end_time >= NOW() AND is_popup = 1 ORDER BY sort_order DESC LIMIT 1"
            );

            if ($activity) {
                success([
                    'id' => $activity['id'],
                    'title' => $activity['title'],
                    'image' => $activity['image'],
                    'link' => $activity['link'] ?? '',
                    'content' => $activity['content'] ?? ''
                ]);
            } else {
                success(null);
            }
        } else {
            // Mock数据：显示活动弹窗
            success([
                'id' => 1,
                'title' => '新用户福利',
                'image' => '/img/activity-popup.jpg',
                'link' => '/newbie-bonus.html',
                'content' => '新用户注册即送1000元体验金！'
            ]);
        }
    }
}
