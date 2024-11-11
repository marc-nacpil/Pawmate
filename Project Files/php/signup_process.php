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
	
	// Database connection
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
	
	try {
		$conn = connectDB();
		
		// Validate input
		$required_fields = ['username', 'password', 'firstname', 'lastname', 'address', 'age', 'sex', 'occupation', 'phone', 'preferred_pet'];
		
		foreach ($required_fields as $field) {
			if (!isset($_POST[$field]) || empty($_POST[$field])) {
				throw new Exception("Missing required field: $field");
			}
		}
	
		// Sanitize input
		$username = $conn->real_escape_string($_POST['username']);
		$password = $conn->real_escape_string($_POST['password']);
		$firstname = $conn->real_escape_string($_POST['firstname']);
		$lastname = $conn->real_escape_string($_POST['lastname']);
		$address = $conn->real_escape_string($_POST['address']);
		$age = intval($_POST['age']);
		$sex = $conn->real_escape_string($_POST['sex']);
		$occupation = $conn->real_escape_string($_POST['occupation']);
		$phone = $conn->real_escape_string($_POST['phone']);
		$preferred_pet = $conn->real_escape_string($_POST['preferred_pet']);
	
		// Check if username exists
		$stmt = $conn->prepare("SELECT id FROM users_table WHERE username = ?");
		$stmt->bind_param("s", $username);
		$stmt->execute();
		$result = $stmt->get_result();
		
		if ($result->num_rows > 0) {
			throw new Exception("Username already exists");
		}
		$stmt->close();
	
		// Insert new user
		$sql = "INSERT INTO users_table (username, password, firstname, lastname, address, age, sex, occupation, phone, preferred_pet) 
				VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
		
		$stmt = $conn->prepare($sql);
		$stmt->bind_param("sssssissss", 
			$username, 
			$password, 
			$firstname, 
			$lastname, 
			$address, 
			$age, 
			$sex, 
			$occupation, 
			$phone, 
			$preferred_pet
		);
		
		if (!$stmt->execute()) {
			throw new Exception("Error inserting user: " . $stmt->error);
		}
	
		$stmt->close();
		$conn->close();
		
		// Redirect on success
		header("Location: ../html_css/login.html?success=1");
		exit();
	
	} catch (Exception $e) {
		error_log($e->getMessage());
		header("Location: ../html_css/signup.html?error=" . urlencode($e->getMessage()));
		exit();
	}
?>