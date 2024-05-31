<?php
session_start();
require 'config/database.php';

if (isset($_SESSION['user_id']) && isset($_POST['comment']) && isset($_POST['post_id'])) {
    $user_id = $_SESSION['user_id'];
    $comment_content = $_POST['comment'];
    $post_id = $_POST['post_id'];

    $query = "INSERT INTO comments (user_id, post_id, content) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iis", $user_id, $post_id, $comment_content);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Не вдалося додати коментар']);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'error' => 'Недостатньо даних для додавання коментаря']);
}