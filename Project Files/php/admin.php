<?php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

function connectDB() {
    $host = "localhost";
    $username = "root";
    $password = "";
    $database = "demo";
    
    try {
        $conn = new mysqli($host, $username, $password, $database);
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }
        return $conn;
    } catch (Exception $e) {
        error_log($e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Database connection error']);
        exit();
    }
}

try {
    $conn = connectDB();

    // Get user activity logs with additional information
    $logs_sql = "SELECT 
        l.username,
        u.firstname,
        cd.city,
        u.preferred_pet,
        cd.ip_address,
        cd.device_type,
        cd.device_model,
        cd.os_name,
        cd.browser_name
    FROM log_table l
    LEFT JOIN users_table u ON l.username = u.username
    LEFT JOIN client_demographics cd ON l.id = cd.login_id
    WHERE u.is_admin = 0 OR u.is_admin IS NULL
    ORDER BY l.login_date DESC, l.login_time DESC
    LIMIT 100";  // Added limit for better performance

    $logs_result = $conn->query($logs_sql);
    
    // Check if query was successful
    if (!$logs_result) {
        throw new Exception("Query failed: " . $conn->error);
    }

    $logs = [];
    while ($row = $logs_result->fetch_assoc()) {
        $logs[] = [
            'username' => htmlspecialchars($row['username'] ?? ''),
            'firstname' => htmlspecialchars($row['firstname'] ?? ''),
            'city' => htmlspecialchars($row['city'] ?? 'N/A'),
            'preferred_pet' => htmlspecialchars($row['preferred_pet'] ?? 'N/A'),
            'ip_address' => htmlspecialchars($row['ip_address'] ?? 'N/A'),
            'device_type' => htmlspecialchars($row['device_type'] ?? 'N/A'),
            'device_model' => htmlspecialchars($row['device_model'] ?? 'N/A'),
            'os_name' => htmlspecialchars($row['os_name'] ?? 'N/A'),
            'browser_name' => htmlspecialchars($row['browser_name'] ?? 'N/A')
        ];
    }

    // Get total non-admin users count
    $users_count = $conn->query("SELECT COUNT(*) as total FROM users_table WHERE is_admin = 0")->fetch_assoc()['total'];

    // Get today's login count for non-admin users
    $today = date('Y-m-d');
    $today_logins = $conn->query("
        SELECT COUNT(*) as total 
        FROM log_table l 
        JOIN users_table u ON l.username = u.username 
        WHERE l.login_date = '$today' AND u.is_admin = 0
    ")->fetch_assoc()['total'];

    // Add debug information
    $response = [
        'logs' => $logs,
        'users_count' => $users_count,
        'today_logins' => $today_logins,
        'debug' => [
            'sql' => $logs_sql,
            'row_count' => count($logs)
        ]
    ];

    echo json_encode($response);

} catch (Exception $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => 'Server error',
        'message' => $e->getMessage(),
        'debug' => isset($logs_sql) ? $logs_sql : 'Query not set'
    ]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>