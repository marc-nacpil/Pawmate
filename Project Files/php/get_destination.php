<?php
session_start();
header('Content-Type: application/json');

if (isset($_SESSION['final_destination'])) {
    $destination = $_SESSION['final_destination'];
    unset($_SESSION['final_destination']); // Clean up
    echo json_encode(['destination' => $destination]);
} else {
    echo json_encode(['destination' => './html_css/login.html']); // Default fallback
}
?>