<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header("Location: login.html");
    exit();
}
require_once '../database/config.php';

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM students");
    $total_students = $stmt->fetchColumn();
    
    // Recent students for user view - Sorted by Department, Course, Year, Block
    $stmt = $pdo->query("SELECT * FROM students ORDER BY created_at DESC LIMIT 5");
    $recent_students = $stmt->fetchAll();
} catch (PDOException $e) {
    $total_students = 0;
    $recent_students = [];
}

// Department Colors Mapping
$dept_colors = [
    'Computer Studies Department' => '#e91e63', // Pink
    'Engineering Department' => '#dc3545', // Red
    'Nursing Department' => '#6f42c1', // Purple
    'Entrepreneurship Department' => '#28a745', // Green
    'Technology Department' => '#ffc107', // Yellow
    'Education Department' => '#007bff' // Blue
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard | BU SMS</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header_offcanvas.php'; ?>

    <div class="container" style="padding-top: 2rem; padding-bottom: 3rem;">
        
        <!-- Welcome Hero -->
        <div class="dashboard-hero">
             <img src="../backend/image.php?type=user&id=<?php echo $user_id; ?>" onerror="this.src='images/male-placeholder.jpg'" alt="User">
             <div>
                 <h2>Hello, <?php echo htmlspecialchars($username); ?>!</h2>
                 <p>Access student records and manage information seamlessly.</p>
             </div>
        </div>

        <!-- Quick Links Grid -->
        <div class="dashboard-stats-grid">
            <!-- View Students -->
            <a href="department_selection.php" class="stat-card" style="text-decoration: none;">
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
             <a href="profile.php" class="stat-card" style="text-decoration: none; opacity: 0.9;">
                <div class="stat-icon" style="background-color: #6c757d;">
                    <i class="fas fa-id-card"></i>
                </div>
                <div class="stat-info">
                    <h3 style="font-size: 1.5rem;">Profile</h3>
                    <p>Manage Settings</p>
                </div>
            </a>
        </div>

        <!-- Recent Activity Table -->
        <div class="recent-activity-section">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h3 style="margin:0; color: var(--bu-blue);">Students Directory Preview</h3>
                <a href="department_selection.php" class="btn btn-secondary" style="font-size: 0.85rem;">View Full List</a>
            </div>
            
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>ID Number</th>
                            <th>Course</th>
                            <th>Department</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($recent_students) > 0): ?>
                            <?php foreach ($recent_students as $student): 
                                $deptColor = $dept_colors[$student['department']] ?? 'var(--text-secondary)';
                            ?>
                            <tr>
                                <td style="display: flex; align-items: center; gap: 12px;">
                                    <img src="../backend/image.php?type=student&id=<?php echo $student['id']; ?>" class="student-img-thumb" onerror="this.src='images/male-placeholder.jpg'">
                                    <span style="font-weight: 600;"><?php echo htmlspecialchars($student['name']); ?></span>
                                </td>
                                <td><span style="background: #e9ecef; padding: 4px 8px; border-radius: 4px; font-family: monospace; font-size: 0.9rem;"><?php echo htmlspecialchars($student['student_id']); ?></span></td>
                                <td><?php echo htmlspecialchars($student['course']); ?></td>
                                <td><small style="color: <?php echo $deptColor; ?>; font-weight: 600;"><?php echo htmlspecialchars($student['department']); ?></small></td>
                                <td>
                                    <a href="student_dashboard_user.php?id=<?php echo $student['id']; ?>" class="btn btn-secondary" style="padding: 6px 12px; font-size: 0.8rem;">View Details</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align:center; padding: 2rem; color: #999;">No students found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
