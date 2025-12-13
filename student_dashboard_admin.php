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
        $email = trim($_POST['email']);
        $gender = $_POST['gender'];
        $course = trim($_POST['course']);
        $year = $_POST['year_level'];
        
        // Determine Department Logic
        $departments = [
            'Bachelor of Elementary Education' => 'Education Department',
            'Bachelor of Secondary Education Major in English' => 'Education Department',
            'Bachelor of Secondary Education Major in Math' => 'Education Department',
            
            'Bachelor of Science in Automotive Technology' => 'Technology Department',
            'Bachelor of Science in Electrical Technology' => 'Technology Department',
            'Bachelor of Science in Electronics Technology' => 'Technology Department',
            'Bachelor of Science in Mechanical Technology' => 'Technology Department',
            
            'Bachelor of Science in Computer Engineering' => 'Engineering Department',
            'Bachelor of Science in Electronics Engineering' => 'Engineering Department',
            
            'Bachelor of Science in Entrepreneurship' => 'Entrepreneurship Department',
            
            'Bachelor of Science in Information System' => 'Computer Studies Department',
            'Bachelor of Science in Information Technology' => 'Computer Studies Department',
            'Bachelor of Science in Information Technology (Animation)' => 'Computer Studies Department',
            'Bachelor of Science in Computer Science' => 'Computer Studies Department',
            
            'Bachelor of Science in Nursing' => 'Nursing Department'
        ];
        
        $department = $departments[$course] ?? 'Unknown Department';
        
        $block = $_POST['block'];
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
                        $sql = "UPDATE students SET student_id=?, name=?, email=?, gender=?, department=?, course=?, year_level=?, block=?, image_blob=? WHERE id=?";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([$student_id, $name, $email, $gender, $department, $course, $year, $block, $imageBlob, $id]);
                    } else {
                        $sql = "UPDATE students SET student_id=?, name=?, email=?, gender=?, department=?, course=?, year_level=?, block=? WHERE id=?";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([$student_id, $name, $email, $gender, $department, $course, $year, $block, $id]);
                    }
                    $message = "Student updated successfully!";
                    // Refresh data
                    $editMode = true;
                    $stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
                    $stmt->execute([$id]);
                    $student = $stmt->fetch();
                } else {
                    // Create
                    if ($imageBlob === null) {
                        $placeholderPath = 'image/male-placeholder.jpg';
                        if (file_exists($placeholderPath)) {
                            $imageBlob = file_get_contents($placeholderPath);
                        } else {
                            $imageBlob = ''; 
                        }
                    }
                    
                    $sql = "INSERT INTO students (student_id, name, email, gender, department, course, year_level, block, image_blob) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$student_id, $name, $email, $gender, $department, $course, $year, $block, $imageBlob]);
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'header_offcanvas.php'; ?>

    <div class="container" style="padding-top: 3rem; padding-bottom: 3rem; max-width: 800px;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 2rem;">
            <div>
                <h2 style="margin-bottom: 5px;"><?php echo $editMode ? 'Edit Student' : 'Add New Student'; ?></h2>
                <p style="color: var(--text-secondary);">Fill in the details below</p>
            </div>
            <a href="students_list_admin.php" class="btn btn-secondary">Back to List</a>
        </div>

        <?php if ($message): ?>
            <div style="padding: 15px; background: #d4edda; color: #155724; border-radius: 8px; margin-bottom: 20px; border: 1px solid #c3e6cb;">
                <i class="fas fa-check-circle"></i> <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div style="padding: 15px; background: #f8d7da; color: #721c24; border-radius: 8px; margin-bottom: 20px; border: 1px solid #f5c6cb;">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form action="student_dashboard_admin.php" method="POST" enctype="multipart/form-data" class="auth-card" style="max-width: 100%;">
            <input type="hidden" name="id" value="<?php echo $student['id'] ?? ''; ?>">
            
            <div style="text-align: center; margin-bottom: 30px;">
                <div class="profile-upload-container" style="position: relative; display: inline-block; cursor: pointer;">
                    <?php 
                    $imgSrc = ($editMode && !empty($student['image_blob'])) 
                        ? "image.php?type=student&id={$student['id']}" 
                        : "image/male-placeholder.jpg"; 
                    ?>
                    <img src="<?php echo $imgSrc; ?>" style="width: 140px; height: 140px; object-fit: cover; border-radius: 12px; border: 1px solid var(--border-color); box-shadow: var(--shadow-sm);" id="previewImg">
                    <label for="imageUpload" class="profile-upload-overlay" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); border-radius: 12px; display: flex; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.3s; color: white;">
                        <i class="fas fa-camera fa-2x"></i>
                    </label>
                    <input type="file" id="imageUpload" name="image" style="display: none;" accept="image/*" onchange="previewImage(this)">
                </div>
                <p style="margin-top: 10px; color: var(--text-secondary); font-size: 0.9rem;">Upload Student Photo</p>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label>Student ID</label>
                    <input type="text" name="student_id" class="form-control" required value="<?php echo htmlspecialchars($student['student_id'] ?? ''); ?>" placeholder="e.g. 2023-12345">
                </div>

                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="name" class="form-control" required value="<?php echo htmlspecialchars($student['name'] ?? ''); ?>" placeholder="Last Name, First Name M.I.">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" class="form-control" required value="<?php echo htmlspecialchars($student['email'] ?? ''); ?>" placeholder="example@bicol-u.edu.ph">
                </div>

                <div class="form-group">
                    <label>Gender</label>
                    <select name="gender" class="form-control" required>
                        <option value="">Select Gender</option>
                        <option value="Male" <?php echo ($student['gender'] ?? '') === 'Male' ? 'selected' : ''; ?>>Male</option>
                        <option value="Female" <?php echo ($student['gender'] ?? '') === 'Female' ? 'selected' : ''; ?>>Female</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Course</label>
                <select name="course" id="courseSelect" class="form-control" required onchange="updateDepartment()">
                    <option value="">Select Course</option>
                    
                    <optgroup label="Education Department">
                        <option value="Bachelor of Elementary Education">Bachelor of Elementary Education</option>
                        <option value="Bachelor of Secondary Education Major in English">Bachelor of Secondary Education Major in English</option>
                        <option value="Bachelor of Secondary Education Major in Math">Bachelor of Secondary Education Major in Math</option>
                    </optgroup>

                    <optgroup label="Technology Department">
                        <option value="Bachelor of Science in Automotive Technology">Bachelor of Science in Automotive Technology</option>
                        <option value="Bachelor of Science in Electrical Technology">Bachelor of Science in Electrical Technology</option>
                        <option value="Bachelor of Science in Electronics Technology">Bachelor of Science in Electronics Technology</option>
                        <option value="Bachelor of Science in Mechanical Technology">Bachelor of Science in Mechanical Technology</option>
                    </optgroup>

                    <optgroup label="Engineering Department">
                        <option value="Bachelor of Science in Computer Engineering">Bachelor of Science in Computer Engineering</option>
                        <option value="Bachelor of Science in Electronics Engineering">Bachelor of Science in Electronics Engineering</option>
                    </optgroup>

                    <optgroup label="Entrepreneurship Department">
                        <option value="Bachelor of Science in Entrepreneurship">Bachelor of Science in Entrepreneurship</option>
                    </optgroup>

                    <optgroup label="Computer Studies Department">
                        <option value="Bachelor of Science in Information System">Bachelor of Science in Information System</option>
                        <option value="Bachelor of Science in Information Technology">Bachelor of Science in Information Technology</option>
                        <option value="Bachelor of Science in Information Technology (Animation)">Bachelor of Science in Information Technology (Animation)</option>
                        <option value="Bachelor of Science in Computer Science">Bachelor of Science in Computer Science</option>
                    </optgroup>

                    <optgroup label="Nursing Department">
                        <option value="Bachelor of Science in Nursing">Bachelor of Science in Nursing</option>
                    </optgroup>
                </select>
                <script>
                    <?php if ($editMode && !empty($student['course'])): ?>
                        document.getElementById('courseSelect').value = "<?php echo $student['course']; ?>";
                    <?php endif; ?>
                </script>
            </div>

            <div class="form-group">
                <label>Department <small style="font-weight: normal; color: #888;">(Auto-assigned)</small></label>
                <input type="text" id="departmentDisplay" class="form-control" readonly value="<?php echo htmlspecialchars($student['department'] ?? ''); ?>">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
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
                <div class="form-group">
                    <label>Block / Section</label>
                    <select name="block" class="form-control" required>
                        <option value="">Select Block</option>
                        <?php 
                        $blocks = ['A', 'B', 'C'];
                        foreach ($blocks as $blk) {
                            $selected = ($student['block'] ?? '') === $blk ? 'selected' : '';
                            echo "<option value=\"$blk\" $selected>Block $blk</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 14px; margin-top: 10px; font-size: 1.1rem;">
                <i class="fas fa-save"></i> <?php echo $editMode ? 'Update Student Record' : 'Save New Student'; ?>
            </button>
        </form>
    </div>

    <script>
        const courseMap = {
            'Bachelor of Elementary Education': 'Education Department',
            'Bachelor of Secondary Education Major in English': 'Education Department',
            'Bachelor of Secondary Education Major in Math': 'Education Department',
            
            'Bachelor of Science in Automotive Technology': 'Technology Department',
            'Bachelor of Science in Electrical Technology': 'Technology Department',
            'Bachelor of Science in Electronics Technology': 'Technology Department',
            'Bachelor of Science in Mechanical Technology': 'Technology Department',
            
            'Bachelor of Science in Computer Engineering': 'Engineering Department',
            'Bachelor of Science in Electronics Engineering': 'Engineering Department',
            
            'Bachelor of Science in Entrepreneurship': 'Entrepreneurship Department',
            
            'Bachelor of Science in Information System': 'Computer Studies Department',
            'Bachelor of Science in Information Technology': 'Computer Studies Department',
            'Bachelor of Science in Information Technology (Animation)': 'Computer Studies Department',
            'Bachelor of Science in Computer Science': 'Computer Studies Department',
            
            'Bachelor of Science in Nursing': 'Nursing Department'
        };

        function updateDepartment() {
            const course = document.getElementById('courseSelect').value;
            const deptInput = document.getElementById('departmentDisplay');
            deptInput.value = courseMap[course] || '';
        }
        
        document.addEventListener('DOMContentLoaded', updateDepartment);

        function previewImage(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('previewImg').src = e.target.result;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>
</html>
