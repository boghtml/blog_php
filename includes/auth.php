<?php
session_start();
require 'config/database.php';


function register($username, $email, $password) {
    global $conn;
    $password = password_hash($password, PASSWORD_DEFAULT);
    $query = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query); // підготовує запит до виконання
    $stmt->bind_param("sss", $username, $email, $password); // Зв'язує змінні з параметрами в запиті.
    $stmt->execute(); // виконує запит
    if ($stmt->affected_rows == 1) { 
        $user_id = $stmt->insert_id;
        $_SESSION['user_id'] = $user_id;
        return true;
    }
    return false;
}

function login($email, $password) {
    global $conn;
    $query = "SELECT user_id, password FROM users WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result(); // витягнули інфу у вигляді об'єкта mysqli_result
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc(); // fetch_assoc або fetch_array.
        if (password_verify($password, $user['password']))
        {
            $_SESSION['user_id'] = $user['user_id'];
            return true;
        }
    }
    return false;
}

// Перевірка, чи користувач авторизований
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}
?>
