<?php
session_start();
require '../config/database.php';
include '../admin_includes/header.php';

$error = '';
$success = '';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password !== $confirm_password) {
        $error = "Новий пароль та підтвердження не співпадають.";
    } else {
        $sql = "SELECT password FROM admins WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $_SESSION['admin_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $admin = $result->fetch_assoc();

        if (password_verify($current_password, $admin['password'])) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_sql = "UPDATE admins SET password = ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("si", $hashed_password, $_SESSION['admin_id']);
            if ($update_stmt->execute()) {
                $success = "Пароль успішно змінено.";
            } else {
                $error = "Помилка при оновленні пароля.";
            }
        } else {
            $error = "Поточний пароль невірний.";
        }
        $stmt->close();
    }
}
?>

<div class="container">
    <hr class="invis">
    <h1>Зміна паролю адміністратора</h1>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    <form method="post" action="">
        <div class="form-group">
            <label for="username">Логін (тільки для перевірки):</label>
            <input type="text" name="username" class="form-control" id="username" required>
        </div>
        <div class="form-group">
            <label for="current_password">Поточний пароль:</label>
            <input type="password" name="current_password" class="form-control" id="current_password" required>
        </div>
        <div class="form-group">
            <label for="new_password">Новий пароль:</label>
            <input type="password" name="new_password" class="form-control" id="new_password" required>
        </div>
        <div class="form-group">
            <label for="confirm_password">Підтвердіть новий пароль:</label>
            <input type="password" name="confirm_password" class="form-control" id="confirm_password" required>
        </div>
        <button type="submit" class="btn btn-primary">Змінити пароль</button>
        <a href="login.php" class="btn btn-link">Повернутися до авторизації</a>
    </form>
</div>

<hr class="invis">

<?php include '../admin_includes/footer.php'; ?>
