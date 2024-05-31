<?php
// Підключення до бази даних
include '../config/database.php';

// Перевірка авторизації адміністратора
session_start();
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'error' => 'Неавторизований доступ']);
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Отримуємо дані з форми
    $post_id = $_POST['id'];
    $category_id = $_POST['category'];
    $title = $_POST['title'];
    $description = $_POST['description'];

    // Початкова ініціалізація шляху до зображення
    $image_path = '';

    // Якщо нове зображення завантажено
    if (!empty($_FILES['image']['name'])) {
        $image = $_FILES['image']['name'];
        $image_tmp = $_FILES['image']['tmp_name'];
        $image_path = '../upload/' . $image;
        move_uploaded_file($image_tmp, $image_path);
    } else {
        // Якщо зображення не вибрано, беремо поточне значення з бази
        $query = "SELECT image FROM posts WHERE post_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $post_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $image_path = $row['image'];
        $stmt->close();
    }

    // Оновлюємо запис у базі
    $update_query = "UPDATE posts SET category_id = ?, title = ?, description = ?, image = ? WHERE post_id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("isssi", $category_id, $title, $description, $image_path, $post_id);

    if ($stmt->execute()) {
        $response['success'] = true;
    } else {
        $response['error'] = 'Помилка при оновленні поста: ' . $conn->error;
    }
    $stmt->close();
}

echo json_encode($response);
?>