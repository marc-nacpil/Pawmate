<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Allow POST requests
header('Access-Control-Allow-Methods: POST');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die('Method Not Allowed');
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
        die("Database connection error");
    }
}

// Verify Username and Password
try {
    $conn = connectDB();
    
    if (!isset($_POST['username']) || !isset($_POST['password'])) {
        throw new Exception("Username and password are required");
    }
    
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    // Modified query to include preferred_pets
    $stmt = $conn->prepare("SELECT * FROM users_table WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($user = $result->fetch_assoc()) {
        if ($password === $user['password']) {  // Note: You should use password_hash() in production
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['is_admin'] = $user['is_admin'];
            $_SESSION['preferred_pet'] = $user['preferred_pet']; // Store pet preference in session
            
            // Log the login
            $date = date('Y-m-d');
            $time = date('H:i:s');
            $log_stmt = $conn->prepare("INSERT INTO log_table (user_id, username, login_date, login_time) VALUES (?, ?, ?, ?)");
            $log_stmt->bind_param("isss", $user['id'], $username, $date, $time);
            $log_stmt->execute();
            $log_stmt->close();
            
            // Store the final destination in session
            $_SESSION['final_destination'] = 
                ($user['username'] === "admin")
                ? '../html_css/admin_dashboard.html'
                : '../PawMate-web/home.html';
        
            // Redirect to intermediate page
            header("Location: ../html_css/collect_info.html?status=success");
            exit();
        }
    }
    
    // Redirect with error message for invalid username/password
    header("Location: ../html_css/login.html?status=error");
    exit();
    
} catch (Exception $e) {
    error_log($e->getMessage());
    header("Location: ../html_css/login.html?status=error");
    exit();
}
?>