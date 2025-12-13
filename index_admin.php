<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.html");
    exit();
}
require_once 'config.php';

// Stats logic
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM students");
    $student_count = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    $user_count = $stmt->fetchColumn();

    // Recent Students
    $stmt = $pdo->query("SELECT * FROM students ORDER BY created_at DESC LIMIT 5");
    $recent_students = $stmt->fetchAll();
} catch (PDOException $e) {
    $student_count = 0;
    $user_count = 0;
    $recent_students = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | BU SMS</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'header_offcanvas.php'; ?>

    <div class="container" style="padding-top: 2rem; padding-bottom: 3rem;">
        
        <!-- Welcome Hero Section -->
        <div class="dashboard-hero">
            <img src="image.php?type=user&id=<?php echo $_SESSION['user_id']; ?>" onerror="this.src='image/male-placeholder.jpg'" alt="Admin">
            <div>
                <h2>Welcome back, Admin!</h2>
                <p>Manage your students and system users efficiently from this dashboard.</p>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="dashboard-stats-grid">
            <!-- Students Stat -->
            <div class="stat-card">
                <div class="stat-icon" style="background-color: var(--bu-blue);">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $student_count; ?></h3>
                    <p>Total Students</p>
                </div>
            </div>

            <!-- Users Stat -->
            <div class="stat-card">
                <div class="stat-icon" style="background-color: var(--bu-orange);">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $user_count; ?></h3>
                    <p>System Users</p>
                </div>
            </div>

            <!-- Action Stat -->
            <a href="student_dashboard_admin.php" class="stat-card" style="text-decoration: none; border-left: 4px solid #28a745;">
                <div class="stat-icon" style="background-color: #28a745;">
                    <i class="fas fa-plus"></i>
                </div>
                <div class="stat-info">
                    <h3 style="font-size: 1.5rem; color: #28a745;">Add New</h3>
                    <p>Student Entry</p>
                </div>
            </a>
        </div>

        <!-- Recent Activity -->
        <div class="recent-activity-section">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h3 style="margin:0; color: var(--bu-blue);">Recently Added Students</h3>
                <a href="students_list_admin.php" class="btn btn-secondary" style="font-size: 0.85rem;">View All Directory</a>
            </div>
            
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>ID Number</th>
                            <th>Course</th>
                            <th>Department</th>
                            <th>Date Added</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($recent_students) > 0): ?>
                            <?php foreach ($recent_students as $student): ?>
                            <tr>
                                <td style="display: flex; align-items: center; gap: 12px;">
                                    <img src="image.php?type=student&id=<?php echo $student['id']; ?>" class="student-img-thumb" onerror="this.src='image/male-placeholder.jpg'">
                                    <span style="font-weight: 600;"><?php echo htmlspecialchars($student['name']); ?></span>
                                </td>
                                <td><span style="background: #e9ecef; padding: 4px 8px; border-radius: 4px; font-family: monospace; font-size: 0.9rem;"><?php echo htmlspecialchars($student['student_id']); ?></span></td>
                                <td><?php echo htmlspecialchars($student['course']); ?></td>
                                <td><small style="color: var(--text-secondary);"><?php echo htmlspecialchars($student['department']); ?></small></td>
                                <td><?php echo date('M d, Y', strtotime($student['created_at'])); ?></td>
                                <td>
                                    <a href="student_dashboard_admin.php?action=edit&id=<?php echo $student['id']; ?>" class="btn btn-primary" style="padding: 6px 12px; font-size: 0.8rem;"><i class="fas fa-edit"></i> Edit</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align:center; padding: 2rem; color: #999;">No recent students found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
