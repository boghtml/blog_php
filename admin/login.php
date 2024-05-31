<?php
session_start();
include '../admin_includes/header.php';
include '../config/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT id, username, password FROM admins WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            $_SESSION['admin_id'] = $row['id'];
            header("Location: admin.php");
            exit;
        } else {
            $error = "Невірний пароль";
        }
    } else {
        $error = "Невірний логін";
    }

    $stmt->close();
}

?>

<body>
    <div id="wrapper">
        <hr class="invis">
        <div class="container">
            <h1>Вхід адміністратора</h1>
            <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
                <div class="form-group">
                    <label for="username">Логін:</label>
                    <input type="text" name="username" class="form-control" id="username" required>
                </div>
                <div class="form-group">
                    <label for="password">Пароль:</label>
                    <input type="password" name="password" class="form-control" id="password" required>
                </div>
                <button type="submit" class="btn btn-primary">Увійти</button>
                <a href="change_password.php" class="btn btn-link">Змінити пароль</a>
            </form>
        </div>
        <hr class="invis">
    </div>
</body>

<?php
include '../admin_includes/footer.php';
?>