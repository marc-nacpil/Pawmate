<?php
	// db_connection.php

	$host = 'localhost';     // Your database host
	$username = 'root';      // Your database username
	$password = '';          // Your database password
	$database = 'demo';   // Your database name

	// Create connection
	$db_connection = new mysqli($host, $username, $password, $database);

	// Check connection
	if ($db_connection->connect_error) {
		die("Connection failed: " . $db_connection->connect_error);
	}

	// Set charset to utf8mb4
	$db_connection->set_charset("utf8mb4");

	// Optionally set timezone
	date_default_timezone_set('Your/Timezone');

	return $db_connection;
?>

