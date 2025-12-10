<?php
require_once 'config.php';

// Check for required parameters
if (!isset($_GET['type']) || !isset($_GET['id'])) {
    header("HTTP/1.0 404 Not Found");
    exit();
}

$type = $_GET['type'];
$id = $_GET['id'];

// Validate ID
if (!is_numeric($id)) {
    header("HTTP/1.0 400 Bad Request");
    exit();
}

try {
    if ($type === 'student') {
        // Fetch student image
        $stmt = $pdo->prepare("SELECT image_blob FROM students WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        if ($row && !empty($row['image_blob'])) {
            // Detect MIME type? 
            // BLOBs usually don't store MIME type separately unless you have a column for it.
            // For simplicity, browsers are good at sniffing, but we should try to output a generic header 
            // or detect signatures if we want to be strict.
            // A standard approach without mime column is to just output image/jpeg or image/png
            // and let the browser handle it.
            header("Content-Type: image/jpeg"); 
            echo $row['image_blob'];
            exit();
        }
    } elseif ($type === 'user') {
        // Fetch user profile image
        $stmt = $pdo->prepare("SELECT profile_image FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        if ($row && !empty($row['profile_image'])) {
            header("Content-Type: image/jpeg");
            echo $row['profile_image'];
            exit();
        }
    }
} catch (PDOException $e) {
    // Silent fail or 404
}

// Fallback image if not found or empty
// We can either redirect to a placeholder or output one
// Let's redirect to a placeholder file based on type, or read it and output it.
// Reading and outputting ensures the URL remains image.php?...

$placeholder = 'image/male-placeholder.jpg'; // Default
if (file_exists($placeholder)) {
    header("Content-Type: image/jpeg");
    readfile($placeholder);
} else {
    // If no placeholder file exists, output a 1x1 pixel or nothing
    header("HTTP/1.0 404 Not Found");
}
?>

