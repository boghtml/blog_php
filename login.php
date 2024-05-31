<?php 
include 'includes/auth.php'; 

// Обробка форм
if ($_SERVER['REQUEST_METHOD'] == 'POST') // Чи було наслано запит POST від форми ?
{
    if (isset($_POST['login'])) 
    {
        if (login($_POST['email'], $_POST['password'])) {
            header("Location: garden-index.php");
            exit;
        } else {
            $error = "Invalid login credentials.";
        }
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
                <div class="card-header">Login</div>
                <div class="card-body">
                    <form action="" method="POST">
                        <div class="form-group">
                            <label for="email">Email:</label>
                            <input type="email" name="email" required class="form-control" placeholder="Enter email">
                        </div>
                        <div class="form-group">
                            <label for="password">Password:</label>
                            <input type="password" name="password" required class="form-control" placeholder="Enter password">
                        </div>
                        <?php if (isset($error)) echo "<p class='text-danger'>$error</p>"; ?>
                        <button type="submit" name="login" class="btn btn-primary">Login</button>
                        <p class="mt-2">Don't have an account? <a href="register.php">Register here</a></p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<hr class="invis1">
<?php include 'includes/footer.php'; ?>
