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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'header_offcanvas.php'; ?>

    <div class="container" style="padding-top: 2rem; padding-bottom: 2rem;">
        <!-- Welcome Header -->
        <div class="dashboard-header">
            <div style="flex: 1;">
                <h2 style="margin-bottom: 5px;">Admin Dashboard</h2>
                <p>Overview of system statistics and recent activities.</p>
            </div>
            <div style="text-align: right;">
                <p style="font-weight: bold;"><?php echo date('F j, Y'); ?></p>
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
            <a href="student_dashboard_admin.php" class="stat-card" style="text-decoration: none; border-left: 5px solid #28a745;">
                <div class="stat-icon" style="background-color: #28a745;">
                    <i class="fas fa-plus"></i>
                </div>
                <div class="stat-info">
                    <h3 style="font-size: 1.5rem;">Add New</h3>
                    <p>Student Entry</p>
                </div>
            </a>
        </div>

        <!-- Recent Activity -->
        <div class="recent-activity-section">
            <div class="section-header">
                <h3>Recently Added Students</h3>
                <a href="students_list_admin.php" class="btn btn-secondary" style="font-size: 0.8rem; padding: 5px 15px;">View All</a>
            </div>
            
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Student ID</th>
                            <th>Name</th>
                            <th>Course</th>
                            <th>Date Added</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($recent_students) > 0): ?>
                            <?php foreach ($recent_students as $student): ?>
                            <tr>
                                <td>
                                    <img src="image.php?type=student&id=<?php echo $student['id']; ?>" class="profile-img-small" onerror="this.src='image/male-placeholder.jpg'">
                                </td>
                                <td><?php echo htmlspecialchars($student['student_id']); ?></td>
                                <td><?php echo htmlspecialchars($student['name']); ?></td>
                                <td><?php echo htmlspecialchars($student['course']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($student['created_at'])); ?></td>
                                <td>
                                    <a href="student_dashboard_admin.php?action=edit&id=<?php echo $student['id']; ?>" style="color: var(--bu-blue);"><i class="fas fa-edit"></i></a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align:center;">No recent activity.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
