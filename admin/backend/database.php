<?php
/**
 * Providence Admin Backend - 数据库操作类
 */

if (!defined('ADMIN_API')) {
    die('Access Denied');
}

class Database {
    private static $instance = null;
    private $pdo = null;
    
    private function __construct() {
        try {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                DB_HOST, DB_PORT, DB_NAME, DB_CHARSET
            );
            
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
        } catch (PDOException $e) {
            if (DEBUG_MODE) {
                throw new Exception('数据库连接失败: ' . $e->getMessage());
            }
            throw new Exception('数据库连接失败');
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getPdo() {
        return $this->pdo;
    }
    
    /**
     * 查询单条记录
     */
    public function fetchOne($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }
    
    /**
     * 查询多条记录
     */
    public function fetchAll($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * 执行SQL
     */
    public function execute($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }
    
    /**
     * 获取最后插入ID
     */
    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }
    
    /**
     * 开始事务
     */
    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }
    
    /**
     * 提交事务
     */
    public function commit() {
        return $this->pdo->commit();
    }
    
    /**
     * 回滚事务
     */
    public function rollback() {
        return $this->pdo->rollBack();
    }
    
    /**
     * 分页查询
     */
    public function paginate($table, $where = [], $page = 1, $pageSize = PAGE_SIZE, $orderBy = 'id DESC') {
        $page = max(1, (int)$page);
        $offset = ($page - 1) * $pageSize;
        
        // 允许的SQL操作符白名单
        $allowedOperators = ['=', '>', '<', '>=', '<=', '!=', '<>', 'LIKE', 'NOT LIKE', 'IN', 'NOT IN'];
        
        $whereClause = '';
        $params = [];
        
        if (!empty($where)) {
            $conditions = [];
            foreach ($where as $key => $value) {
                if (is_array($value)) {
                    // 支持操作符: ['status', '=', 1] 或 ['amount', '>', 100]
                    $operator = strtoupper(trim($value[1]));
                    if (!in_array($operator, $allowedOperators)) {
                        throw new Exception("不支持的操作符: {$value[1]}");
                    }
                    $conditions[] = "`{$value[0]}` $operator ?";
                    $params[] = $value[2];
                } else {
                    $conditions[] = "`$key` = ?";
                    $params[] = $value;
                }
            }
            $whereClause = 'WHERE ' . implode(' AND ', $conditions);
        }
        
        // 获取总数
        $countSql = "SELECT COUNT(*) as total FROM `$table` $whereClause";
        $total = $this->fetchOne($countSql, $params)['total'];
        
        // 获取数据
        $dataSql = "SELECT * FROM `$table` $whereClause ORDER BY $orderBy LIMIT $pageSize OFFSET $offset";
        $items = $this->fetchAll($dataSql, $params);
        
        return [
            'total' => (int)$total,
            'page' => $page,
            'page_size' => $pageSize,
            'total_pages' => ceil($total / $pageSize),
            'items' => $items
        ];
    }
    
    /**
     * 插入数据
     */
    public function insert($table, $data) {
        $fields = array_keys($data);
        $placeholders = array_fill(0, count($fields), '?');
        
        $sql = sprintf(
            "INSERT INTO `%s` (`%s`) VALUES (%s)",
            $table,
            implode('`, `', $fields),
            implode(', ', $placeholders)
        );
        
        $this->execute($sql, array_values($data));
        return $this->lastInsertId();
    }
    
    /**
     * 更新数据
     */
    public function update($table, $data, $where) {
        $sets = [];
        $params = [];
        
        foreach ($data as $key => $value) {
            $sets[] = "`$key` = ?";
            $params[] = $value;
        }
        
        $conditions = [];
        foreach ($where as $key => $value) {
            $conditions[] = "`$key` = ?";
            $params[] = $value;
        }
        
        $sql = sprintf(
            "UPDATE `%s` SET %s WHERE %s",
            $table,
            implode(', ', $sets),
            implode(' AND ', $conditions)
        );
        
        return $this->execute($sql, $params);
    }
    
    /**
     * 删除数据
     */
    public function delete($table, $where) {
        $conditions = [];
        $params = [];
        
        foreach ($where as $key => $value) {
            $conditions[] = "`$key` = ?";
            $params[] = $value;
        }
        
        $sql = sprintf(
            "DELETE FROM `%s` WHERE %s",
            $table,
            implode(' AND ', $conditions)
        );
        
        return $this->execute($sql, $params);
    }
}

// 全局数据库实例
function db() {
    return Database::getInstance();
}
