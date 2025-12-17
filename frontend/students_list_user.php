<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header("Location: login.html");
    exit();
}
require_once '../database/config.php';

// Filter & Sort Parameters
$department_filter = $_GET['department'] ?? '';
$course_filter = $_GET['course'] ?? ''; // Renamed/Added course filter
$year_filter = $_GET['year'] ?? '';
$block_filter = $_GET['block'] ?? '';
$gender_filter = $_GET['gender'] ?? '';
$sort_by = $_GET['sort'] ?? 'created_at';
$order = $_GET['order'] ?? 'DESC';

// Base Query
$sql = "SELECT * FROM students WHERE 1=1";
$params = [];

// Apply Filters
if (!empty($department_filter)) {
    $sql .= " AND department = ?";
    $params[] = $department_filter;
}
if (!empty($course_filter)) {
    $sql .= " AND course = ?";
    $params[] = $course_filter;
}
if (!empty($year_filter)) {
    $sql .= " AND year_level = ?";
    $params[] = $year_filter;
}
if (!empty($block_filter)) {
    $sql .= " AND block = ?";
    $params[] = $block_filter;
}
if (!empty($gender_filter)) {
    $sql .= " AND gender = ?";
    $params[] = $gender_filter;
}

// Apply Sorting
$allowed_sorts = ['name', 'year_level', 'block', 'gender', 'created_at', 'updated_at', 'course'];
if (in_array($sort_by, $allowed_sorts)) {
    $sql .= " ORDER BY $sort_by $order";
} else {
    $sql .= " ORDER BY created_at DESC";
}

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $students = $stmt->fetchAll();
    
     // Get unique courses for the current department filter, or all unique courses if no department selected
    if (!empty($department_filter)) {
        $course_stmt = $pdo->prepare("SELECT DISTINCT course FROM students WHERE department = ? ORDER BY course");
        $course_stmt->execute([$department_filter]);
    } else {
        $course_stmt = $pdo->query("SELECT DISTINCT course FROM students ORDER BY course");
    }
    $courses = $course_stmt->fetchAll(PDO::FETCH_COLUMN);

} catch (PDOException $e) {
    $students = [];
    $courses = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Students List | BU SMS</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header_offcanvas.php'; ?>

    <div class="container" style="padding-top: 2.5rem; padding-bottom: 3rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <div>
                <h2 style="margin-bottom: 5px;">Students Directory</h2>
                <p style="color: var(--text-secondary);">
                    <?php if($department_filter): ?>
                        Viewing students in <strong><?php echo htmlspecialchars($department_filter); ?></strong>
                    <?php else: ?>
                        Browse student records
                    <?php endif; ?>
                </p>
            </div>
            <div style="display: flex; gap: 10px;">
                <a href="javascript:history.back()" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
                <?php
                    $addLink = "student_dashboard_user.php";
                    if ($department_filter) {
                        $addLink .= "?department=" . urlencode($department_filter);
                    }
                ?>
                <a href="<?php echo $addLink; ?>" class="btn btn-primary"><i class="fas fa-plus-circle"></i> Add New Student</a>
            </div>
        </div>
        
        <!-- Advanced Filter & Sort Bar -->
        <div class="filter-bar-container">
            <?php if($department_filter): ?>
                <div style="width: 100%; padding-bottom: 15px; margin-bottom: 15px; border-bottom: 1px solid var(--border-color); font-size: 1.1rem; font-weight: 600; color: var(--bu-blue);">
                    <i class="fas fa-building"></i> <?php echo htmlspecialchars($department_filter); ?>
                </div>
            <?php endif; ?>
            <form method="GET" action="students_list_user.php" class="filter-form">
                <?php if($department_filter): ?>
                    <input type="hidden" name="department" value="<?php echo htmlspecialchars($department_filter); ?>">
                <?php endif; ?>
                
                <!-- Filters Group -->
                <div class="filter-group">
                    <div class="filter-item">
                        <label>Course</label>
                        <select name="course" class="filter-select" onchange="this.form.submit()">
                            <option value="">All Courses</option>
                            <?php foreach ($courses as $crs): ?>
                                <option value="<?php echo htmlspecialchars($crs); ?>" <?php echo $course_filter === $crs ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($crs); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="filter-item">
                        <label>Year Level</label>
                        <select name="year" class="filter-select" onchange="this.form.submit()">
                            <option value="">All Years</option>
                            <option value="1" <?php echo $year_filter === '1' ? 'selected' : ''; ?>>1st Year</option>
                            <option value="2" <?php echo $year_filter === '2' ? 'selected' : ''; ?>>2nd Year</option>
                            <option value="3" <?php echo $year_filter === '3' ? 'selected' : ''; ?>>3rd Year</option>
                            <option value="4" <?php echo $year_filter === '4' ? 'selected' : ''; ?>>4th Year</option>
                        </select>
                    </div>

                    <div class="filter-item">
                        <label>Block</label>
                        <select name="block" class="filter-select" onchange="this.form.submit()">
                            <option value="">All Blocks</option>
                            <option value="A" <?php echo $block_filter === 'A' ? 'selected' : ''; ?>>Block A</option>
                            <option value="B" <?php echo $block_filter === 'B' ? 'selected' : ''; ?>>Block B</option>
                            <option value="C" <?php echo $block_filter === 'C' ? 'selected' : ''; ?>>Block C</option>
                        </select>
                    </div>

                    <div class="filter-item">
                        <label>Gender</label>
                        <select name="gender" class="filter-select" onchange="this.form.submit()">
                            <option value="">All Genders</option>
                            <option value="Male" <?php echo $gender_filter === 'Male' ? 'selected' : ''; ?>>Male</option>
                            <option value="Female" <?php echo $gender_filter === 'Female' ? 'selected' : ''; ?>>Female</option>
                        </select>
                    </div>
                </div>

                <!-- Separator -->
                <div class="filter-separator"></div>

                <!-- Sort Group -->
                <div class="filter-group">
                    <div class="filter-item">
                        <label>Sort By</label>
                        <select name="sort" class="filter-select" onchange="this.form.submit()">
                            <option value="created_at" <?php echo $sort_by === 'created_at' ? 'selected' : ''; ?>>Date Added</option>
                            <option value="updated_at" <?php echo $sort_by === 'updated_at' ? 'selected' : ''; ?>>Recently Modified</option>
                            <option value="name" <?php echo $sort_by === 'name' ? 'selected' : ''; ?>>Name</option>
                            <option value="year_level" <?php echo $sort_by === 'year_level' ? 'selected' : ''; ?>>Year Level</option>
                            <option value="block" <?php echo $sort_by === 'block' ? 'selected' : ''; ?>>Block</option>
                            <option value="gender" <?php echo $sort_by === 'gender' ? 'selected' : ''; ?>>Gender</option>
                        </select>
                    </div>

                    <div class="filter-item">
                        <label>Order</label>
                        <select name="order" class="filter-select" onchange="this.form.submit()">
                            <option value="ASC" <?php echo $order === 'ASC' ? 'selected' : ''; ?>>Ascending</option>
                            <option value="DESC" <?php echo $order === 'DESC' ? 'selected' : ''; ?>>Descending</option>
                        </select>
                    </div>
                </div>

                <div class="filter-reset">
                    <a href="students_list_user.php<?php echo $department_filter ? '?department='.urlencode($department_filter) : ''; ?>" class="btn-reset" title="Reset Filters"><i class="fas fa-redo"></i> Reset</a>
                </div>

            </form>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Student Profile</th>
                        <th>ID Number</th>
                        <th>Email / Gender</th>
                        <th>Academic Info</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($students) > 0): ?>
                        <?php foreach ($students as $student): ?>
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center; gap: 15px;">
                                    <img src="../backend/image.php?type=student&id=<?php echo $student['id']; ?>" class="student-img-thumb" style="width: 50px; height: 50px;" onerror="this.src='images/male-placeholder.jpg'">
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
                                <a href="student_dashboard_user.php?id=<?php echo $student['id']; ?>" class="btn btn-secondary" style="padding: 8px 16px; font-size: 0.85rem;"><i class="fas fa-eye"></i> View</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align:center; padding: 3rem; color: #999;">
                                <i class="fas fa-folder-open fa-3x" style="margin-bottom: 1rem; opacity: 0.5;"></i>
                                <p>No students found matching your criteria.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
