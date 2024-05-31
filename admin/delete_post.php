<?php
session_start();
include '../config/database.php'; // Підключення до БД

if (isset($_SESSION['admin_id']) && isset($_POST['id'])) {
    $post_id = $_POST['id'];

    $query = "DELETE FROM posts WHERE post_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $post_id);
    $result = $stmt->execute();

    if ($result) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $conn->error]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
}
?>
