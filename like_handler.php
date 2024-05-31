<?php
session_start();
require 'config/database.php';

$post_id = json_decode(file_get_contents('php://input'))->post_id; // перетворює json в об'єкт php і бере post_id
$user_id = $_SESSION['user_id'];

$check_query = "SELECT * FROM likes WHERE post_id = ? AND user_id = ?";
$stmt = $conn->prepare($check_query);
$stmt->bind_param('ii', $post_id, $user_id);
$stmt->execute(); 
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    // Додавання лайка
    $insert_query = "INSERT INTO likes (post_id, user_id) VALUES (?, ?)";
    $insert_stmt = $conn->prepare($insert_query);
    $insert_stmt->bind_param('ii', $post_id, $user_id);
    $insert_stmt->execute();
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
?>
