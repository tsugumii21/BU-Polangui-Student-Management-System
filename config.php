<?php
/**
 * Database Configuration
 * 
 * REQUIRED DATABASE STRUCTURE:
 * 
 * 1. Create a database named 'student_management_db'
 * 
 * 2. Table: users
 *    - id (INT, AUTO_INCREMENT, PRIMARY KEY)
 *    - username (VARCHAR(50), UNIQUE, NOT NULL)
 *    - password (VARCHAR(255), NOT NULL)
 *    - role (ENUM('admin', 'user'), DEFAULT 'user')
 *    - profile_image (LONGBLOB, NULL) -- For user avatar
 *    - created_at (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP)
 * 
 * 3. Table: students
 *    - id (INT, AUTO_INCREMENT, PRIMARY KEY)
 *    - student_id (VARCHAR(20), UNIQUE, NOT NULL)
 *    - name (VARCHAR(100), NOT NULL)
 *    - email (VARCHAR(100), NOT NULL)
 *    - gender (ENUM('Male', 'Female'), NOT NULL)
 *    - department (VARCHAR(100), NOT NULL) -- Added field
 *    - course (VARCHAR(100), NOT NULL)
 *    - year_level (INT, NOT NULL)
 *    - block (VARCHAR(10), NOT NULL) -- Changed to letters (A, B, C)
 *    - image_blob (LONGBLOB, NOT NULL) -- For student photo
 *    - created_at (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP)
 */

$host = 'localhost';
$dbname = 'student_management_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    
    // Set PDO to throw exceptions on error
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Set default fetch mode to associative array
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}

// Global Error Handling Configuration (Optional but recommended)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>
