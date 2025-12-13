<?php
require_once '../database/config.php';

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
        // Fetch student image and gender
        $stmt = $pdo->prepare("SELECT image_blob, gender FROM students WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        if ($row && !empty($row['image_blob'])) {
            header("Content-Type: image/jpeg"); 
            echo $row['image_blob'];
            exit();
        } else if ($row) {
            // Found student but no image_blob, serve placeholder based on gender
            $gender = $row['gender'] ?? 'Male'; // Default to Male if null
            $placeholder = ($gender === 'Female') ? '../frontend/images/female-placeholder.jpg' : '../frontend/images/male-placeholder.jpg';
            
            if (file_exists($placeholder)) {
                header("Content-Type: image/jpeg");
                readfile($placeholder);
                exit();
            }
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

// Fallback image if not found or empty (and no student found logic triggered)
// For users or general errors, default to male placeholder or a generic one
$placeholder = '../frontend/images/male-placeholder.jpg'; 
if (file_exists($placeholder)) {
    header("Content-Type: image/jpeg");
    readfile($placeholder);
} else {
    header("HTTP/1.0 404 Not Found");
}
?>
