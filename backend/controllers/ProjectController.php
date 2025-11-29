<?php
/**
 * 项目控制器 - 投资项目列表和详情
 */

class ProjectController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * 获取项目列表
     */
    public function getList() {
        $pagination = getPagination();
        $category = getQuery('category');
        $currency = getQuery('currency', 'CNY');

        if ($this->db && $this->db->isConnected()) {
            $where = "status = 1";
            $params = [];

            if ($category) {
                $where .= " AND category = ?";
                $params[] = $category;
            }

            if ($currency) {
                $where .= " AND (currency = ? OR currency IS NULL)";
                $params[] = $currency;
            }

            $total = $this->db->count('projects', $where, $params);

            // 使用参数绑定防止SQL注入
            $offset = intval($pagination['offset']);
            $pageSize = intval($pagination['pageSize']);
            $sql = "SELECT * FROM projects WHERE $where ORDER BY sort_order DESC, id DESC LIMIT ?, ?";
            $params[] = $offset;
            $params[] = $pageSize;
            $projects = $this->db->fetchAll($sql, $params);

            $items = array_map(function($p) {
                return [
                    'id' => $p['id'],
                    'title' => $p['title'],
                    'subtitle' => $p['subtitle'] ?? '',
                    'cover_image' => $p['cover_image'] ?? '',
                    'category' => $p['category'] ?? '',
                    'currency' => $p['currency'] ?? 'CNY',
                    'min_invest' => formatMoney($p['min_invest']),
                    'max_invest' => formatMoney($p['max_invest'] ?? 0),
                    'daily_rate' => ($p['daily_rate'] * 100) . '%',
                    'total_days' => (int)$p['total_days'],
                    'total_return_rate' => ($p['total_return_rate'] ?? 0) . '%',
                    'risk_level' => (int)$p['risk_level'],
                    'invest_count' => (int)$p['invest_count'],
                    'total_invested' => formatMoney($p['total_invested'] ?? 0),
                    'status' => (int)$p['status']
                ];
            }, $projects);

            success(paginateResponse($items, $total, $pagination['page'], $pagination['pageSize']));
        } else {
            // Mock数据
            $mockProjects = [
                [
                    'id' => 1,
                    'title' => '稳健理财7天',
                    'subtitle' => '短期灵活，收益稳定',
                    'cover_image' => '/img/project1.jpg',
                    'category' => 'short',
                    'currency' => 'CNY',
                    'min_invest' => '1000.00',
                    'max_invest' => '100000.00',
                    'daily_rate' => '0.12%',
                    'total_days' => 7,
                    'total_return_rate' => '0.84%',
                    'risk_level' => 1,
                    'invest_count' => 1256,
                    'total_invested' => '15680000.00',
                    'status' => 1
                ],
                [
                    'id' => 2,
                    'title' => '进取理财30天',
                    'subtitle' => '月度理财，收益更高',
                    'cover_image' => '/img/project2.jpg',
                    'category' => 'medium',
                    'currency' => 'CNY',
                    'min_invest' => '5000.00',
                    'max_invest' => '500000.00',
                    'daily_rate' => '0.15%',
                    'total_days' => 30,
                    'total_return_rate' => '4.5%',
                    'risk_level' => 2,
                    'invest_count' => 856,
                    'total_invested' => '28500000.00',
                    'status' => 1
                ],
                [
                    'id' => 3,
                    'title' => '长期收益90天',
                    'subtitle' => '季度理财，收益翻倍',
                    'cover_image' => '/img/project3.jpg',
                    'category' => 'long',
                    'currency' => 'CNY',
                    'min_invest' => '10000.00',
                    'max_invest' => '1000000.00',
                    'daily_rate' => '0.18%',
                    'total_days' => 90,
                    'total_return_rate' => '16.2%',
                    'risk_level' => 2,
                    'invest_count' => 456,
                    'total_invested' => '45600000.00',
                    'status' => 1
                ],
                [
                    'id' => 4,
                    'title' => 'USDT理财7天',
                    'subtitle' => 'USDT稳定收益',
                    'cover_image' => '/img/project4.jpg',
                    'category' => 'short',
                    'currency' => 'USDT',
                    'min_invest' => '100.00',
                    'max_invest' => '10000.00',
                    'daily_rate' => '0.10%',
                    'total_days' => 7,
                    'total_return_rate' => '0.7%',
                    'risk_level' => 1,
                    'invest_count' => 328,
                    'total_invested' => '1250000.00',
                    'status' => 1
                ],
                [
                    'id' => 5,
                    'title' => 'USDT进阶30天',
                    'subtitle' => 'USDT高收益理财',
                    'cover_image' => '/img/project5.jpg',
                    'category' => 'medium',
                    'currency' => 'USDT',
                    'min_invest' => '500.00',
                    'max_invest' => '50000.00',
                    'daily_rate' => '0.12%',
                    'total_days' => 30,
                    'total_return_rate' => '3.6%',
                    'risk_level' => 2,
                    'invest_count' => 189,
                    'total_invested' => '980000.00',
                    'status' => 1
                ]
            ];

            // 按币种过滤
            if ($currency) {
                $mockProjects = array_filter($mockProjects, function($p) use ($currency) {
                    return $p['currency'] === $currency;
                });
                $mockProjects = array_values($mockProjects);
            }

            success(paginateResponse($mockProjects, count($mockProjects), $pagination['page'], $pagination['pageSize']));
        }
    }

    /**
     * 获取项目详情
     */
    public function getDetail() {
        $id = getQuery('id');
        if (!$id) {
            error('项目ID不能为空');
        }

        if ($this->db && $this->db->isConnected()) {
            $project = $this->db->fetch("SELECT * FROM projects WHERE id = ?", [$id]);
            if (!$project) {
                error('项目不存在');
            }

            // 增加浏览次数
            $this->db->query("UPDATE projects SET view_count = view_count + 1 WHERE id = ?", [$id]);

            // 获取项目经理
            $manager = null;
            if ($project['manager_id']) {
                $manager = $this->db->fetch("SELECT id, name, title, avatar, bio FROM project_managers WHERE id = ?", [$project['manager_id']]);
            }

            success([
                'id' => $project['id'],
                'title' => $project['title'],
                'subtitle' => $project['subtitle'] ?? '',
                'description' => $project['description'] ?? '',
                'cover_image' => $project['cover_image'] ?? '',
                'images' => json_decode($project['images'] ?? '[]', true),
                'category' => $project['category'] ?? '',
                'currency' => $project['currency'] ?? 'CNY',
                'min_invest' => formatMoney($project['min_invest']),
                'max_invest' => formatMoney($project['max_invest'] ?? 0),
                'daily_rate' => ($project['daily_rate'] * 100) . '%',
                'total_days' => (int)$project['total_days'],
                'total_return_rate' => ($project['total_return_rate'] ?? 0) . '%',
                'risk_level' => (int)$project['risk_level'],
                'invest_count' => (int)$project['invest_count'],
                'view_count' => (int)$project['view_count'],
                'total_invested' => formatMoney($project['total_invested'] ?? 0),
                'status' => (int)$project['status'],
                'manager' => $manager,
                'created_at' => $project['created_at']
            ]);
        } else {
            // Mock数据
            $mockProjects = [
                1 => [
                    'id' => 1,
                    'title' => '稳健理财7天',
                    'subtitle' => '短期灵活，收益稳定',
                    'description' => '本产品为短期理财产品，投资周期为7天，日收益率0.12%，到期自动返还本金及收益。适合追求稳健收益的投资者。',
                    'cover_image' => '/img/project1.jpg',
                    'images' => ['/img/project1-1.jpg', '/img/project1-2.jpg'],
                    'category' => 'short',
                    'currency' => 'CNY',
                    'min_invest' => '1000.00',
                    'max_invest' => '100000.00',
                    'daily_rate' => '0.12%',
                    'total_days' => 7,
                    'total_return_rate' => '0.84%',
                    'risk_level' => 1,
                    'invest_count' => 1256,
                    'view_count' => 15680,
                    'total_invested' => '15680000.00',
                    'status' => 1,
                    'manager' => [
                        'id' => 1,
                        'name' => '张明',
                        'title' => '高级理财顾问',
                        'avatar' => '/img/manager1.jpg',
                        'bio' => '10年金融从业经验，专注稳健理财'
                    ],
                    'created_at' => '2024-01-01 00:00:00'
                ],
                2 => [
                    'id' => 2,
                    'title' => '进取理财30天',
                    'subtitle' => '月度理财，收益更高',
                    'description' => '30天投资周期，日收益率0.15%，适合中期理财需求。',
                    'cover_image' => '/img/project2.jpg',
                    'images' => [],
                    'category' => 'medium',
                    'currency' => 'CNY',
                    'min_invest' => '5000.00',
                    'max_invest' => '500000.00',
                    'daily_rate' => '0.15%',
                    'total_days' => 30,
                    'total_return_rate' => '4.5%',
                    'risk_level' => 2,
                    'invest_count' => 856,
                    'view_count' => 9800,
                    'total_invested' => '28500000.00',
                    'status' => 1,
                    'manager' => null,
                    'created_at' => '2024-01-15 00:00:00'
                ]
            ];

            if (!isset($mockProjects[$id])) {
                // 返回默认项目
                $project = $mockProjects[1];
                $project['id'] = $id;
            } else {
                $project = $mockProjects[$id];
            }

            success($project);
        }
    }

    /**
     * 获取项目分类
     */
    public function getCategories() {
        success([
            'items' => [
                ['id' => 'short', 'name' => '短期理财', 'description' => '7天以内'],
                ['id' => 'medium', 'name' => '中期理财', 'description' => '30天左右'],
                ['id' => 'long', 'name' => '长期理财', 'description' => '90天以上']
            ]
        ]);
    }
}
