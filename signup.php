<?php
session_start();
require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Basic Validation
    if (empty($username) || empty($password)) {
        header("Location: signup.html?error=empty");
        exit();
    }

    if ($password !== $confirm_password) {
        header("Location: signup.html?error=mismatch");
        exit();
    }

    // Check if user exists
    try {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            header("Location: signup.html?error=exists");
            exit();
        }

        // Create new user
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        // Default role is 'user'. 
        // Note: For 'admin' creation, you'd typically do it manually in DB or have a secret code.
        // The prompt says "admin (user: admin, pass: 1234)". We can pre-seed this or let the user create it.
        // I will just implement standard signup as 'user'.

        $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'user')");
        if ($stmt->execute([$username, $hashed_password])) {
            header("Location: login.html?error=created");
            exit();
        } else {
            header("Location: signup.html?error=system");
            exit();
        }

    } catch (PDOException $e) {
        error_log("Signup Error: " . $e->getMessage());
        header("Location: signup.html?error=system");
        exit();
    }
} else {
    header("Location: signup.html");
    exit();
}
?>

