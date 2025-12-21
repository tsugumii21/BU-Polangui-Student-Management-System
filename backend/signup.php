<?php
session_start();
require_once '../database/config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Basic Validation
    if (empty($username) || empty($password) || empty($email)) {
        header("Location: ../frontend/signup.html?error=empty");
        exit();
    }

    if ($password !== $confirm_password) {
        header("Location: ../frontend/signup.html?error=mismatch");
        exit();
    }

    // Check if user exists
    try {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            header("Location: ../frontend/signup.html?error=exists");
            exit();
        }

        // Create new user
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        // Default role is 'user'. 

        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'user')");
        if ($stmt->execute([$username, $email, $hashed_password])) {
            header("Location: ../frontend/login.html?error=created");
            exit();
        } else {
            header("Location: ../frontend/signup.html?error=system");
            exit();
        }

    } catch (PDOException $e) {
        error_log("Signup Error: " . $e->getMessage());
        header("Location: ../frontend/signup.html?error=system");
        exit();
    }
} else {
    header("Location: ../frontend/signup.html");
    exit();
}
?>
