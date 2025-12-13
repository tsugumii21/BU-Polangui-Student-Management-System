<?php
session_start();
require_once '../database/config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        header("Location: ../frontend/login.html?error=empty");
        exit();
    }

    try {
        $stmt = $pdo->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Login Success
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            // Redirect based on role
            if ($user['role'] === 'admin') {
                header("Location: ../frontend/index_admin.php");
            } else {
                header("Location: ../frontend/index_user.php");
            }
            exit();
        } else {
            // Invalid credentials
            header("Location: ../frontend/login.html?error=invalid");
            exit();
        }
    } catch (PDOException $e) {
        // Log error and show generic message
        error_log("Login Error: " . $e->getMessage());
        header("Location: ../frontend/login.html?error=system");
        exit();
    }
} else {
    header("Location: ../frontend/login.html");
    exit();
}
?>

