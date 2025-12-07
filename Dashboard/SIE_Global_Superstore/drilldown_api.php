<?php
require 'config.php';

header('Content-Type: application/json');

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);

$level = $input['level'] ?? 'region';
$metric = $input['metric'] ?? 'profit';
$filters = $input['filters'] ?? [];

// Fungsi untuk mendapatkan data berdasarkan level drilldown
function getDrilldownData($pdo, $level, $metric, $filters) {
    $select = '';
    $groupBy = '';
    $where = [];
    $params = [];
    
    // Tentukan kolom berdasarkan level
    switch($level) {
        case 'region':
            $select = 'region';
            $groupBy = 'region';
            break;
        case 'country':
            $select = 'country';
            $groupBy = 'country';
            if (isset($filters['region'])) {
                $where[] = 'region = :region';
                $params[':region'] = $filters['region'];
            }
            break;
        case 'state':
            $select = 'state';
            $groupBy = 'state';
            if (isset($filters['region'])) {
                $where[] = 'region = :region';
                $params[':region'] = $filters['region'];
            }
            if (isset($filters['country'])) {
                $where[] = 'country = :country';
                $params[':country'] = $filters['country'];
            }
            break;
        case 'city':
            $select = 'city';
            $groupBy = 'city';
            if (isset($filters['region'])) {
                $where[] = 'region = :region';
                $params[':region'] = $filters['region'];
            }
            if (isset($filters['country'])) {
                $where[] = 'country = :country';
                $params[':country'] = $filters['country'];
            }
            if (isset($filters['state'])) {
                $where[] = 'state = :state';
                $params[':state'] = $filters['state'];
            }
            break;
    }
    
    // Tentukan metric dan query
    $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
    
    if ($metric === 'ratio') {
        $sql = "SELECT $select as label, 
                SUM(profit) as profit, 
                SUM(sales) as sales,
                (SUM(profit) / NULLIF(SUM(sales), 0)) * 100 as value
                FROM global_superstore 
                $whereClause 
                GROUP BY $groupBy 
                HAVING sales > 0
                ORDER BY value DESC 
                LIMIT 15";
    } else {
        $metricCol = $metric === 'profit' ? 'profit' : 'sales';
        $sql = "SELECT $select as label, SUM($metricCol) as value
                FROM global_superstore 
                $whereClause 
                GROUP BY $groupBy 
                ORDER BY value DESC 
                LIMIT 15";
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    return $results;
}

try {
    $data = getDrilldownData($pdo, $level, $metric, $filters);
    echo json_encode($data);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>