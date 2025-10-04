<?php
// save_post.php
session_start();
require 'db.php';
header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['message' => 'Please login to save articles.']);
    exit;
}
$user_id = $_SESSION['user_id'];
$post_id = (int)$_POST['post_id'];
$stmt = $pdo->prepare("INSERT INTO saved_posts (user_id, post_id) VALUES (?, ?)");
if ($stmt->execute([$user_id, $post_id])) {
    echo json_encode(['message' => 'Article saved to your profile!']);
} else {
    echo json_encode(['message' => 'Error saving article.']);
}
?>
