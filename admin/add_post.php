<?php
// Підключення до бази даних
include 'database.php';

$message = ''; // Повідомлення про результат


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_post'])) {
    $category = $_POST['category'];
    $title = $_POST['title'];
    $description = $_POST['description'];

    $image = $_FILES['image']['name'];
    $image_tmp = $_FILES['image']['tmp_name'];
    $image_path = '' . $image;
    move_uploaded_file($image_tmp, $image_path);

    $sql = "INSERT INTO posts (category_id, title, description, image) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isss", $category, $title, $description, $image_path);
    if ($stmt->execute()) {
        $message = 'Новий пост успішно додано!';
    } else {
        $message = 'Помилка при додаванні поста: ' . $conn->error;
    }
    $stmt->close();
}
?>
