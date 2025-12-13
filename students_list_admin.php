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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'header_offcanvas.php'; ?>

    <div class="container" style="padding-top: 2.5rem; padding-bottom: 3rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <div>
                <h2>Students Directory</h2>
                <p style="color: var(--text-secondary);">Manage and view all student records</p>
            </div>
            <a href="student_dashboard_admin.php" class="btn btn-primary"><i class="fas fa-plus-circle"></i> Add New Student</a>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Student Profile</th>
                        <th>ID Number</th>
                        <th>Email / Gender</th>
                        <th>Academic Info</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($students) > 0): ?>
                        <?php foreach ($students as $student): ?>
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center; gap: 15px;">
                                    <img src="image.php?type=student&id=<?php echo $student['id']; ?>" class="student-img-thumb" style="width: 50px; height: 50px;" onerror="this.src='image/male-placeholder.jpg'">
                                    <div>
                                        <div style="font-weight: 600; color: var(--bu-blue);"><?php echo htmlspecialchars($student['name']); ?></div>
                                        <div style="font-size: 0.85rem; color: var(--text-secondary);">Added: <?php echo date('M d, Y', strtotime($student['created_at'])); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td><span style="background: #e9ecef; padding: 5px 10px; border-radius: 6px; font-family: monospace; font-weight: 600;"><?php echo htmlspecialchars($student['student_id']); ?></span></td>
                            <td>
                                <div style="font-size: 0.9rem;"><?php echo htmlspecialchars($student['email'] ?? ''); ?></div>
                                <div style="font-size: 0.8rem; color: var(--text-secondary); margin-top: 2px;"><i class="fas fa-venus-mars"></i> <?php echo htmlspecialchars($student['gender'] ?? ''); ?></div>
                            </td>
                            <td>
                                <div style="font-weight: 600; font-size: 0.9rem;"><?php echo htmlspecialchars($student['course']); ?></div>
                                <div style="font-size: 0.8rem; color: var(--text-secondary); margin-top: 2px;"><?php echo htmlspecialchars($student['department'] ?? ''); ?></div>
                                <div style="margin-top: 4px;">
                                    <span style="background: #e3f2fd; color: var(--bu-blue); padding: 2px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 600;">Year <?php echo htmlspecialchars($student['year_level']); ?></span>
                                    <span style="background: #fff3cd; color: #856404; padding: 2px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 600; margin-left: 5px;">Block <?php echo htmlspecialchars($student['block'] ?? ''); ?></span>
                                </div>
                            </td>
                            <td>
                                <div style="display: flex; gap: 8px;">
                                    <a href="student_dashboard_admin.php?action=edit&id=<?php echo $student['id']; ?>" class="btn btn-secondary" style="padding: 8px 12px; font-size: 0.85rem;" title="Edit"><i class="fas fa-edit"></i></a>
                                    <form action="student_dashboard_admin.php" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this student?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $student['id']; ?>">
                                        <button type="submit" class="btn btn-danger" style="padding: 8px 12px; font-size: 0.85rem;" title="Delete"><i class="fas fa-trash-alt"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align:center; padding: 3rem; color: #999;">
                                <i class="fas fa-folder-open fa-3x" style="margin-bottom: 1rem; opacity: 0.5;"></i>
                                <p>No students found in the directory.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
