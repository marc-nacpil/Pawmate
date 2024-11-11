<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Connect to the Database
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
    // Get the POST data
    $json_data = file_get_contents('php://input');
    $client_info = json_decode($json_data, true);
    
    if (!$client_info) {
        throw new Exception("Invalid client information provided");
    }
    
    $conn = connectDB();
    
    // Get the latest login_id from log_table for this user
    $user_id = $_SESSION['user_id'];
    $username = $_SESSION['username'];
    
    $log_query = "SELECT id FROM log_table WHERE user_id = ? ORDER BY id DESC LIMIT 1";
    $log_stmt = $conn->prepare($log_query);
    $log_stmt->bind_param("i", $user_id);
    $log_stmt->execute();
    $log_result = $log_stmt->get_result();
    $log_row = $log_result->fetch_assoc();
    $login_id = $log_row['id'];

    $stmt = $conn->prepare("INSERT INTO client_demographics (
        user_id, 
        username, 
        ip_address, 
        city,
        region,
        country,
        country_code,
        isp,
        vpn_detected,
        vpn_type,
        device_type, 
        device_model, 
        browser_name,
        browser_version, 
        os_name, 
        os_version, 
        login_id
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    // Extract location information
    $city = $client_info['location']['city'] ?? 'Unknown';
    $region = $client_info['location']['region'] ?? 'Unknown';
    $country = $client_info['location']['country'] ?? 'Unknown';
    $country_code = $client_info['location']['countryCode'] ?? 'XX';
    
    // Extract ISP information
    $isp = $client_info['isp']['isp'] ?? 'Unknown';
    
    // Extract VPN information
    $vpn_detected = $client_info['vpn']['detected'] ?? false;
    $vpn_type = $client_info['vpn']['type'] ?? 'Unknown';
    
    // Extract other client information
    $ip_address = $client_info['ip'] ?? 'Unknown';
    $device_type = $client_info['device'] ?? 'Unknown';
    $device_model = $client_info['model'] ?? 'Unknown';
    $browser_name = $client_info['browser']['name'] ?? 'Unknown';
    $browser_version = $client_info['browser']['version'] ?? 'Unknown';
    $os_name = $client_info['os']['name'] ?? 'Unknown';
    $os_version = $client_info['os']['version'] ?? 'Unknown';
    
    $stmt->bind_param("isssssssisssssssi",
        $user_id,      // integer
        $username,     // string
        $ip_address,   // string
        $city,         // string
        $region,       // string
        $country,      // string
        $country_code, // string
        $isp,          // string
        $vpn_detected, // integer (boolean)
        $vpn_type,     // string
        $device_type,  // string
        $device_model, // string
        $browser_name, // string
        $browser_version, // string
        $os_name,      // string
        $os_version,   // string
        $login_id      // integer
    );
    
    $stmt->execute();
    
    echo json_encode([
        'success' => true,
        'message' => 'Client information stored successfully'
    ]);
    
} catch (Exception $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => 'Error storing client information',
        'message' => $e->getMessage()
    ]);
}
?>