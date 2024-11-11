<?php
	session_start();
	header('Content-Type: application/json');

	// Check if user is logged in
	if (!isset($_SESSION['user_id'])) {
		http_response_code(401);
		echo json_encode(['error' => 'User not logged in']);
		exit;
	}

	// Function to connect to database (reuse your existing function)
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
		// If preference is already in session, return it
		if (isset($_SESSION['preferred_pet'])) {
			echo json_encode(['preferred_pet' => $_SESSION['preferred_pet']]);
			exit;
		}
		
		// Otherwise, fetch from database
		$conn = connectDB();
		$stmt = $conn->prepare("SELECT preferred_pet FROM users_table WHERE id = ?");
		$stmt->bind_param("i", $_SESSION['user_id']);
		$stmt->execute();
		$result = $stmt->get_result();
		
		if ($user = $result->fetch_assoc()) {
			// Store in session for future use
			$_SESSION['preferred_pet'] = $user['preferred_pet'];
			echo json_encode(['preferred_pet' => $user['preferred_pet']]);
		} else {
			http_response_code(404);
			echo json_encode(['error' => 'User preference not found']);
		}
		
	} catch (Exception $e) {
		error_log($e->getMessage());
		http_response_code(500);
		echo json_encode(['error' => 'Server error']);
	}
?>