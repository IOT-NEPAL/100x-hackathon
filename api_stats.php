<?php
/**
 * API endpoint for live platform statistics
 * Returns JSON data for dynamic stats display
 */

require_once 'includes/db.php';

header('Content-Type: application/json');

try {
    // Get platform statistics
    $stats = [];
    
    // Count total users
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE is_active = 1");
    $stats['total_users'] = $stmt->fetch()['count'];
    
    // Count opportunities
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM opportunities WHERE is_active = 1");
    $stats['total_opportunities'] = $stmt->fetch()['count'];
    
    // Count applications
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM applications");
    $stats['total_applications'] = $stmt->fetch()['count'];
    
    // Format network size for display (e.g., "15K", "2.5M")
    $network_size = $stats['total_users'];
    if ($network_size >= 1000000) {
        $formatted_size = number_format($network_size / 1000000, 1) . 'M';
    } elseif ($network_size >= 1000) {
        $formatted_size = number_format($network_size / 1000, 1) . 'K';
    } else {
        $formatted_size = (string)$network_size;
    }
    
    // Return stats for different pages
    $response = [
        'success' => true,
        'index' => [
            'network_size' => $formatted_size,
            'total_jobs' => number_format($stats['total_opportunities']),
            'total_applications' => number_format($stats['total_applications'])
        ],
        'raw' => $stats
    ];
    
    echo json_encode($response);
    
} catch (PDOException $e) {
    // Return fallback values on error
    $response = [
        'success' => false,
        'error' => 'Unable to fetch stats',
        'index' => [
            'network_size' => '15K',
            'total_jobs' => '1,200',
            'total_applications' => '5,000'
        ]
    ];
    
    echo json_encode($response);
}
?>



