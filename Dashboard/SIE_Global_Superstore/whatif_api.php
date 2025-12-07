<?php
require 'config.php';

header('Content-Type: application/json');

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

if ($action === 'getRegionData') {
    $region = $input['region'] ?? '';
    
    if (empty($region)) {
        http_response_code(400);
        echo json_encode(['error' => 'Region tidak boleh kosong']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("
            SELECT 
                SUM(profit) as total_profit,
                SUM(sales) as total_sales
            FROM global_superstore 
            WHERE region = :region
        ");
        
        $stmt->execute([':region' => $region]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($data) {
            echo json_encode([
                'profit' => $data['total_profit'],
                'sales' => $data['total_sales']
            ]);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Data tidak ditemukan']);
        }
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Action tidak valid']);
}
?>