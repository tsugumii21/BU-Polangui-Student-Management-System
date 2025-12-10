<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header("Location: login.html");
    exit();
}
require_once 'config.php';

$message = '';
$error = '';
$viewMode = false;
$student = null;

// Handle GET for View Mode
if (isset($_GET['id'])) {
    $viewMode = true;
    $id = $_GET['id'];
    try {
        $stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
        $stmt->execute([$id]);
        $student = $stmt->fetch();
        if (!$student) {
            header("Location: students_list_user.php");
            exit();
        }
    } catch (PDOException $e) {
        die($e->getMessage());
    }
}

// Handle POST for Create (Users can only Add, not Edit/Delete usually, or based on prompt "Add students or View details")
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = trim($_POST['student_id']);
    $name = trim($_POST['name']);
    $course = trim($_POST['course']);
    $year = $_POST['year_level'];
    
    // Image Handling
    $imageBlob = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $imageBlob = file_get_contents($_FILES['image']['tmp_name']);
    }

    if (empty($student_id) || empty($name)) {
        $error = "Student ID and Name are required.";
    } else {
        try {
             // Require image or placeholder
             if ($imageBlob === null) {
                $placeholderPath = 'image/male-placeholder.jpg';
                if (file_exists($placeholderPath)) {
                    $imageBlob = file_get_contents($placeholderPath);
                } else {
                    $imageBlob = ''; 
                }
            }
            
            $sql = "INSERT INTO students (student_id, name, course, year_level, image_blob) VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$student_id, $name, $course, $year, $imageBlob]);
            $message = "Student added successfully!";
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $error = "Error: Student ID already exists.";
            } else {
                $error = "Database Error: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $viewMode ? 'Student Details' : 'Add Student'; ?> | BU SMS</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'header_offcanvas.php'; ?>

    <div class="container" style="padding-top: 2rem; max-width: 800px;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 2rem;">
            <h2><?php echo $viewMode ? 'Student Details' : 'Add New Student'; ?></h2>
            <a href="students_list_user.php" class="btn btn-secondary">Back to List</a>
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

        <form action="student_dashboard_user.php" method="POST" enctype="multipart/form-data" class="auth-card" style="max-width: 100%;">
            
            <div style="text-align: center; margin-bottom: 20px;">
                <?php if ($viewMode): ?>
                    <img src="image.php?type=student&id=<?php echo $student['id']; ?>" style="width: 150px; height: 150px; object-fit: cover; border-radius: 8px; border: 4px solid var(--bu-light-gray);">
                <?php else: ?>
                    <div class="form-group" style="text-align: left;">
                        <label>Student Photo</label>
                        <input type="file" name="image" class="form-control" accept="image/*" required>
                    </div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label>Student ID</label>
                <input type="text" name="student_id" class="form-control" value="<?php echo htmlspecialchars($student['student_id'] ?? ''); ?>" <?php echo $viewMode ? 'readonly' : 'required'; ?>>
            </div>

            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($student['name'] ?? ''); ?>" <?php echo $viewMode ? 'readonly' : 'required'; ?>>
            </div>

            <div class="form-group">
                <label>Course</label>
                <?php if ($viewMode): ?>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($student['course']); ?>" readonly>
                <?php else: ?>
                <select name="course" class="form-control" required>
                    <option value="">Select Course</option>
                    <option value="BS Information Technology">BS Information Technology</option>
                    <option value="BS Computer Science">BS Computer Science</option>
                    <option value="BS Information Systems">BS Information Systems</option>
                    <option value="BS Education">BS Education</option>
                    <option value="BS Nursing">BS Nursing</option>
                    <option value="BS Accountancy">BS Accountancy</option>
                </select>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label>Year Level</label>
                <?php if ($viewMode): ?>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($student['year_level']); ?>" readonly>
                <?php else: ?>
                <select name="year_level" class="form-control" required>
                    <option value="1">1</option>
                    <option value="2">2</option>
                    <option value="3">3</option>
                    <option value="4">4</option>
                </select>
                <?php endif; ?>
            </div>

            <?php if (!$viewMode): ?>
                <button type="submit" class="btn btn-primary">Submit Student</button>
            <?php endif; ?>
        </form>
    </div>
</body>
</html>

