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
    
    // Get login logs excluding admin users
    $logs_sql = "SELECT l.username, l.login_date, l.login_time, u.firstname, u.lastname 
                 FROM log_table l 
                 JOIN users_table u ON l.user_id = u.id 
                 WHERE u.is_admin = 0
                 ORDER BY l.login_date DESC, l.login_time DESC";
    $logs_result = $conn->query($logs_sql);
    
    $logs = [];
    while ($row = $logs_result->fetch_assoc()) {
        $logs[] = [
            'username' => htmlspecialchars($row['username']),
            'firstname' => htmlspecialchars($row['firstname']),
            'lastname' => htmlspecialchars($row['lastname']),
            'login_date' => htmlspecialchars($row['login_date']),
            'login_time' => htmlspecialchars($row['login_time'])
        ];
    }
    
    $users_count = $conn->query("SELECT COUNT(*) as total FROM users_table WHERE is_admin = 0")->fetch_assoc()['total'];
    $today = date('Y-m-d');
    $today_logins = $conn->query("SELECT COUNT(*) as total FROM log_table l JOIN users_table u ON l.user_id = u.id WHERE login_date = '$today' AND u.is_admin = 0")->fetch_assoc()['total'];
    
    echo json_encode([
        'logs' => $logs,
        'users_count' => $users_count,
        'today_logins' => $today_logins
    ]);

} catch (Exception $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Server error']);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>

<!-- username, firstname, city, preferred_pet, ip_address, device_type, device_model, os_name, browser_name -->

