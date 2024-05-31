<?php 
include 'includes/auth.php'; 

// Обробка форми реєстрації
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
    $registration_result = register($_POST['username'], $_POST['email'], $_POST['password']);
    if ($registration_result) {
        header("Location: garden-index.php");
        exit;
    } else {
        $error = "Registration failed. Email might be already in use.";
    }
}

include 'includes/header.php';
include 'includes/body_without_nav.php';
?>

<hr class="invis1">

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Register</div>
                <div class="card-body">
                    <form action="" method="POST">
                        <div class="form-group">
                            <label for="username">Username:</label>
                            <input type="text" name="username" required class="form-control" placeholder="Enter username">
                        </div>
                        <div class="form-group">
                            <label for="email">Email:</label>
                            <input type="email" name="email" required class="form-control" placeholder="Enter email">
                        </div>
                        <div class="form-group">
                            <label for="password">Password:</label>
                            <input type="password" name="password" required class="form-control" placeholder="Enter password">
                        </div>
                        <?php if (isset($error)) echo "<p class='text-danger'>$error</p>"; ?>
                        <button type="submit" name="register" class="btn btn-primary">Register</button>
                        <p class="mt-2">Already have an account? <a href="login.php">Login here</a></p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<hr class="invis1">
<?php include 'includes/footer.php'; ?>
