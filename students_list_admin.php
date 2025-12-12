<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.html");
    exit();
}
require_once 'config.php';

// Fetch all students
try {
    $stmt = $pdo->query("SELECT * FROM students ORDER BY created_at DESC");
    $students = $stmt->fetchAll();
} catch (PDOException $e) {
    $students = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Students List (Admin) | BU SMS</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'header_offcanvas.php'; ?>

    <div class="container" style="padding-top: 2rem;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h2>Students List</h2>
            <a href="student_dashboard_admin.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add New</a>
        </div>

        <!-- Search handled by global search or client-side filter for now, or we can add a specific filter here -->
        
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Student ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Gender</th>
                        <th>Department</th>
                        <th>Course</th>
                        <th>Year</th>
                        <th>Block</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($students) > 0): ?>
                        <?php foreach ($students as $student): ?>
                        <tr>
                            <td>
                                <img src="image.php?type=student&id=<?php echo $student['id']; ?>" class="student-img-thumb" onerror="this.src='image/male-placeholder.jpg'">
                            </td>
                            <td><?php echo htmlspecialchars($student['student_id']); ?></td>
                            <td><?php echo htmlspecialchars($student['name']); ?></td>
                            <td><?php echo htmlspecialchars($student['email'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($student['gender'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($student['department'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($student['course']); ?></td>
                            <td><?php echo htmlspecialchars($student['year_level']); ?></td>
                            <td><?php echo htmlspecialchars($student['block'] ?? ''); ?></td>
                            <td>
                                <a href="student_dashboard_admin.php?action=edit&id=<?php echo $student['id']; ?>" class="btn btn-primary" style="padding: 5px 10px; font-size: 0.9rem;">Edit</a>
                                <form action="student_dashboard_admin.php" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this student?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo $student['id']; ?>">
                                    <button type="submit" class="btn btn-danger" style="padding: 5px 10px; font-size: 0.9rem;">Delete</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align:center;">No students found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>

