<?php
require_once '../database/config.php';

header('Content-Type: application/json');

// Only allow logged in users (or admin)
session_start();
if (!isset($_SESSION['role'])) {
    echo json_encode([]);
    exit;
}

$query = $_GET['q'] ?? '';

if (strlen($query) < 2) {
    echo json_encode([]);
    exit;
}

try {
    // Search students by name or ID
    $stmt = $pdo->prepare("SELECT id, name, student_id FROM students WHERE name LIKE ? OR student_id LIKE ? LIMIT 5");
    $searchTerm = "%$query%";
    $stmt->execute([$searchTerm, $searchTerm]);
    $results = $stmt->fetchAll();
    
    // Format for frontend
    $formatted = [];
    foreach ($results as $r) {
        $formatted[] = [
            'id' => $r['id'],
            'text' => $r['name'] . ' (' . $r['student_id'] . ')',
            'url' => ($_SESSION['role'] === 'admin' ? 'student_dashboard_admin.php?action=edit&id=' : 'student_dashboard_user.php?id=') . $r['id']
        ];
    }
    
    echo json_encode($formatted);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>

