<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.html");
    exit();
}
require_once 'config.php';

$editMode = false;
$student = null;
$message = '';
$error = '';

// Handle POST actions (Create/Update/Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'delete') {
        $id = $_POST['id'] ?? 0;
        try {
            $stmt = $pdo->prepare("DELETE FROM students WHERE id = ?");
            $stmt->execute([$id]);
            header("Location: students_list_admin.php"); // Redirect after delete
            exit();
        } catch (PDOException $e) {
            $error = "Error deleting student: " . $e->getMessage();
        }
    } else {
        // Create or Update
        $student_id = trim($_POST['student_id']);
        $name = trim($_POST['name']);
        $course = trim($_POST['course']);
        $year = $_POST['year_level'];
        $id = $_POST['id'] ?? null;
        
        // Image Handling
        $imageBlob = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $imageBlob = file_get_contents($_FILES['image']['tmp_name']);
        }

        if (empty($student_id) || empty($name)) {
            $error = "Student ID and Name are required.";
        } else {
            try {
                if ($id) {
                    // Update
                    if ($imageBlob) {
                        $sql = "UPDATE students SET student_id=?, name=?, course=?, year_level=?, image_blob=? WHERE id=?";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([$student_id, $name, $course, $year, $imageBlob, $id]);
                    } else {
                        $sql = "UPDATE students SET student_id=?, name=?, course=?, year_level=? WHERE id=?";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([$student_id, $name, $course, $year, $id]);
                    }
                    $message = "Student updated successfully!";
                    // Refresh data
                    $editMode = true;
                    $stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
                    $stmt->execute([$id]);
                    $student = $stmt->fetch();
                } else {
                    // Create
                    // Require image for new student? Optional.
                    if ($imageBlob === null) {
                        // Use a default placeholder or just insert null
                        // For this assignment, we want BLOBs. 
                        // Let's create an empty blob or allow null if the schema permits (we set NOT NULL in config comments, but could be flexible).
                        // Let's assume user must upload or we use a placeholder file.
                        // I'll allow NULL or empty string if schema allows, but config said NOT NULL.
                        // So I will read a placeholder if none provided, OR alert user.
                        // Let's just insert empty string for now if allowed, or error.
                        // Actually, let's force upload or use default placeholder content.
                        $placeholderPath = 'image/male-placeholder.jpg';
                        if (file_exists($placeholderPath)) {
                            $imageBlob = file_get_contents($placeholderPath);
                        } else {
                            $imageBlob = ''; // Might fail if strict mode
                        }
                    }
                    
                    $sql = "INSERT INTO students (student_id, name, course, year_level, image_blob) VALUES (?, ?, ?, ?, ?)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$student_id, $name, $course, $year, $imageBlob]);
                    $message = "Student added successfully!";
                }
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) { // Integrity constraint violation (duplicate student_id)
                    $error = "Error: Student ID already exists.";
                } else {
                    $error = "Database Error: " . $e->getMessage();
                }
            }
        }
    }
}

// Handle GET for Edit Mode
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $editMode = true;
    $id = $_GET['id'];
    try {
        $stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
        $stmt->execute([$id]);
        $student = $stmt->fetch();
        if (!$student) {
            header("Location: students_list_admin.php");
            exit();
        }
    } catch (PDOException $e) {
        die($e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $editMode ? 'Edit' : 'Add'; ?> Student | BU SMS</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'header_offcanvas.php'; ?>

    <div class="container" style="padding-top: 2rem; max-width: 800px;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 2rem;">
            <h2><?php echo $editMode ? 'Edit Student' : 'Add New Student'; ?></h2>
            <a href="students_list_admin.php" class="btn btn-secondary">Back to List</a>
        </div>

        <?php if ($message): ?>
            <div style="padding: 15px; background: #d4edda; color: #155724; border-radius: 4px; margin-bottom: 20px;">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div style="padding: 15px; background: #f8d7da; color: #721c24; border-radius: 4px; margin-bottom: 20px;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form action="student_dashboard_admin.php" method="POST" enctype="multipart/form-data" class="auth-card" style="max-width: 100%;">
            <input type="hidden" name="id" value="<?php echo $student['id'] ?? ''; ?>">
            
            <div class="form-group">
                <label>Student Image</label>
                <?php if ($editMode && !empty($student['image_blob'])): ?>
                    <div style="margin-bottom: 10px;">
                        <img src="image.php?type=student&id=<?php echo $student['id']; ?>" style="width: 100px; height: 100px; object-fit: cover; border-radius: 4px;">
                        <p style="font-size: 0.8rem; color: #666;">Current Image</p>
                    </div>
                <?php endif; ?>
                <input type="file" name="image" class="form-control" accept="image/*" <?php echo $editMode ? '' : 'required'; ?>>
                <small style="color: #666;">Upload a new image to replace.</small>
            </div>

            <div class="form-group">
                <label>Student ID</label>
                <input type="text" name="student_id" class="form-control" required value="<?php echo htmlspecialchars($student['student_id'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="name" class="form-control" required value="<?php echo htmlspecialchars($student['name'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label>Course</label>
                <select name="course" class="form-control" required>
                    <option value="">Select Course</option>
                    <?php 
                    $courses = ['BS Information Technology', 'BS Computer Science', 'BS Information Systems', 'BS Education', 'BS Nursing', 'BS Accountancy'];
                    foreach ($courses as $c) {
                        $selected = ($student['course'] ?? '') === $c ? 'selected' : '';
                        echo "<option value=\"$c\" $selected>$c</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="form-group">
                <label>Year Level</label>
                <select name="year_level" class="form-control" required>
                    <?php 
                    for ($i = 1; $i <= 4; $i++) {
                        $selected = ($student['year_level'] ?? '') == $i ? 'selected' : '';
                        echo "<option value=\"$i\" $selected>$i</option>";
                    }
                    ?>
                </select>
            </div>

            <button type="submit" class="btn btn-primary"><?php echo $editMode ? 'Update Student' : 'Add Student'; ?></button>
        </form>
    </div>
</body>
</html>

