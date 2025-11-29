<?php
/**
 * 产品管理控制器
 */

class ProductController {
    /**
     * 产品列表
     */
    public static function list() {
        requireLogin();
        
        list($page, $pageSize) = getPagination();
        
        $where = [];
        $params = [];
        
        // 状态筛选
        $status = input('status');
        if ($status !== null && $status !== '') {
            $where[] = "status = ?";
            $params[] = $status;
        }
        
        // 分类筛选
        $category = input('category');
        if ($category) {
            $where[] = "category = ?";
            $params[] = $category;
        }
        
        // 搜索
        $keyword = input('keyword');
        if ($keyword) {
            $where[] = "title LIKE ?";
            $params[] = "%$keyword%";
        }
        
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        $offset = ($page - 1) * $pageSize;
        
        // 查询总数
        $total = db()->fetchOne(
            "SELECT COUNT(*) as count FROM projects $whereClause",
            $params
        )['count'];
        
        // 查询数据
        $products = db()->fetchAll(
            "SELECT 
                p.*,
                m.name as manager_name
             FROM projects p
             LEFT JOIN project_managers m ON p.manager_id = m.id
             $whereClause
             ORDER BY p.sort_order ASC, p.id DESC
             LIMIT $pageSize OFFSET $offset",
            $params
        );
        
        // 处理图片列表
        foreach ($products as &$p) {
            if ($p['images']) {
                $p['images'] = json_decode($p['images'], true);
            }
        }
        
        success([
            'total' => (int)$total,
            'page' => $page,
            'page_size' => $pageSize,
            'items' => $products
        ]);
    }
    
    /**
     * 产品详情
     */
    public static function detail($id) {
        requireLogin();
        
        $product = db()->fetchOne(
            "SELECT 
                p.*,
                m.name as manager_name, m.title as manager_title, m.avatar as manager_avatar
             FROM projects p
             LEFT JOIN project_managers m ON p.manager_id = m.id
             WHERE p.id = ?",
            [$id]
        );
        
        if (!$product) {
            error('产品不存在');
        }
        
        if ($product['images']) {
            $product['images'] = json_decode($product['images'], true);
        }
        
        // 获取投资统计
        $stats = db()->fetchOne(
            "SELECT 
                COUNT(*) as invest_count,
                COALESCE(SUM(invest_amount), 0) as total_invested,
                COUNT(DISTINCT user_id) as investor_count
             FROM user_investments WHERE project_id = ?",
            [$id]
        );
        
        $product['stats'] = $stats;
        
        success($product);
    }
    
    /**
     * 保存产品（新增/编辑）
     */
    public static function save() {
        requirePermission('product_edit');
        
        $id = input('id');
        
        $data = [
            'title' => input('title'),
            'subtitle' => input('subtitle', ''),
            'description' => input('description', ''),
            'cover_image' => input('cover_image', ''),
            'category' => input('category', ''),
            'min_invest' => input('min_invest', 0),
            'max_invest' => input('max_invest'),
            'daily_rate' => input('daily_rate', 0),
            'total_days' => input('total_days', 0),
            'total_return_rate' => input('total_return_rate'),
            'risk_level' => input('risk_level', 1),
            'manager_id' => input('manager_id'),
            'status' => input('status', 1),
            'sort_order' => input('sort_order', 0)
        ];
        
        // 验证必填字段
        if (empty($data['title'])) {
            error('请填写产品名称');
        }
        if ($data['min_invest'] <= 0) {
            error('请设置最低投资额');
        }
        if ($data['daily_rate'] <= 0) {
            error('请设置日收益率');
        }
        if ($data['total_days'] <= 0) {
            error('请设置投资周期');
        }
        
        // 处理图片列表
        $images = input('images');
        if ($images) {
            $data['images'] = is_array($images) ? json_encode($images) : $images;
        }
        
        if ($id) {
            // 编辑
            $product = db()->fetchOne("SELECT id FROM projects WHERE id = ?", [$id]);
            if (!$product) {
                error('产品不存在');
            }
            
            db()->update('projects', $data, ['id' => $id]);
            logAction('update_product', 'product', "更新产品 ID:$id");
            success(['id' => $id], '更新成功');
        } else {
            // 新增
            $data['created_at'] = date('Y-m-d H:i:s');
            $id = db()->insert('projects', $data);
            logAction('create_product', 'product', "新增产品 ID:$id");
            success(['id' => $id], '添加成功');
        }
    }
    
    /**
     * 切换产品状态
     */
    public static function toggleStatus($id) {
        requirePermission('product_edit');
        
        $product = db()->fetchOne("SELECT id, status FROM projects WHERE id = ?", [$id]);
        if (!$product) {
            error('产品不存在');
        }
        
        $newStatus = $product['status'] == 1 ? 2 : 1;
        
        db()->update('projects', ['status' => $newStatus], ['id' => $id]);
        
        logAction('toggle_product', 'product', [
            'product_id' => $id,
            'old_status' => $product['status'],
            'new_status' => $newStatus
        ]);
        
        success(['status' => $newStatus], $newStatus == 1 ? '已上架' : '已下架');
    }
    
    /**
     * 删除产品
     */
    public static function delete($id) {
        requirePermission('product_delete');
        
        $product = db()->fetchOne("SELECT id FROM projects WHERE id = ?", [$id]);
        if (!$product) {
            error('产品不存在');
        }
        
        // 检查是否有进行中的投资
        $activeInvest = db()->fetchOne(
            "SELECT COUNT(*) as count FROM user_investments WHERE project_id = ? AND status = 1",
            [$id]
        )['count'];
        
        if ($activeInvest > 0) {
            error('该产品有进行中的投资订单，无法删除');
        }
        
        db()->delete('projects', ['id' => $id]);
        
        logAction('delete_product', 'product', "删除产品 ID:$id");
        
        success(null, '删除成功');
    }
}
