<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header("Location: login.html");
    exit();
}
require_once 'config.php';

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM students");
    $total_students = $stmt->fetchColumn();
    
    // Recent students for user view
    $stmt = $pdo->query("SELECT * FROM students ORDER BY created_at DESC LIMIT 5");
    $recent_students = $stmt->fetchAll();
} catch (PDOException $e) {
    $total_students = 0;
    $recent_students = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard | BU SMS</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'header_offcanvas.php'; ?>

    <div class="container" style="padding-top: 2rem; padding-bottom: 2rem;">
        
        <!-- Welcome Header -->
        <div class="dashboard-header">
             <img src="image.php?type=user&id=<?php echo $user_id; ?>" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover; border: 3px solid var(--bu-blue);" onerror="this.src='image/male-placeholder.jpg'">
             <div style="flex: 1;">
                 <h2 style="margin-bottom: 5px;">Hello, <?php echo htmlspecialchars($username); ?>!</h2>
                 <p>Welcome to your dashboard. What would you like to do today?</p>
             </div>
        </div>

        <!-- Quick Links Grid -->
        <div class="dashboard-stats-grid">
            <!-- View Students -->
            <a href="students_list_user.php" class="stat-card" style="text-decoration: none;">
                <div class="stat-icon" style="background-color: var(--bu-blue);">
                    <i class="fas fa-list-ul"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $total_students; ?></h3>
                    <p>Active Students</p>
                </div>
            </a>

            <!-- Add Student -->
            <a href="student_dashboard_user.php" class="stat-card" style="text-decoration: none;">
                <div class="stat-icon" style="background-color: var(--bu-orange);">
                    <i class="fas fa-user-plus"></i>
                </div>
                <div class="stat-info">
                    <h3 style="font-size: 1.5rem;">Register</h3>
                    <p>New Student</p>
                </div>
            </a>
            
             <!-- My Profile -->
             <div class="stat-card" style="opacity: 0.7; cursor: not-allowed;">
                <div class="stat-icon" style="background-color: #6c757d;">
                    <i class="fas fa-id-card"></i>
                </div>
                <div class="stat-info">
                    <h3 style="font-size: 1.5rem;">Profile</h3>
                    <p>Manage Settings</p>
                </div>
            </div>
        </div>

        <!-- Recent Activity Table -->
        <div class="recent-activity-section">
            <div class="section-header">
                <h3>Students Directory Preview</h3>
                <a href="students_list_user.php" class="btn btn-secondary" style="font-size: 0.8rem; padding: 5px 15px;">View Full List</a>
            </div>
            
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Student ID</th>
                            <th>Name</th>
                            <th>Course</th>
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
                                <td>
                                    <a href="student_dashboard_user.php?id=<?php echo $student['id']; ?>" style="color: var(--bu-blue);"><i class="fas fa-eye"></i></a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align:center;">No students found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
