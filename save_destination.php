<?php
// save_destination.php
session_start();
require 'db.php';
header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['message' => 'Please login to save destinations.']);
    exit;
}
$user_id = $_SESSION['user_id'];
$dest_id = (int)$_POST['dest_id'];
$stmt = $pdo->prepare("INSERT INTO saved_destinations (user_id, destination_id) VALUES (?, ?)");
if ($stmt->execute([$user_id, $dest_id])) {
    echo json_encode(['message' => 'Destination saved to your wishlist!']);
} else {
    echo json_encode(['message' => 'Error saving destination.']);
}
?>
