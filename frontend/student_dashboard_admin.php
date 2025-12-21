<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.html");
    exit();
}
require_once '../database/config.php';

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
    } elseif ($action === 'delete_photo') {
        $id = $_POST['id'] ?? 0;
        try {
            // Clear the image blob
            $stmt = $pdo->prepare("UPDATE students SET image_blob = '' WHERE id = ?");
            $stmt->execute([$id]);
            // Redirect back to edit page to refresh the view
            header("Location: student_dashboard_admin.php?action=edit&id=" . $id);
            exit();
        } catch (PDOException $e) {
            $error = "Error removing photo: " . $e->getMessage();
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
                        $placeholderPath = 'images/male-placeholder.jpg';
                        if ($gender === 'Female') {
                            $placeholderPath = 'images/female-placeholder.jpg';
                        }
                        
                        // Note: Backend script runs in backend/, so path to placeholder is relative to backend/
                        // But images are in frontend/images/
                        $placeholderPath = '../frontend/images/male-placeholder.jpg';
                        if ($gender === 'Female') {
                            $placeholderPath = '../frontend/images/female-placeholder.jpg';
                        }

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
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .swal-custom-popup {
            border-radius: 16px !important;
            font-family: 'Inter', sans-serif !important;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important;
        }
        .swal-custom-title {
            color: var(--bu-blue) !important;
            font-weight: 700 !important;
            font-size: 1.4rem !important;
            margin-bottom: 0.5rem !important;
        }
        .swal-custom-text {
            color: var(--text-secondary) !important;
            font-size: 0.95rem !important;
        }
        .swal2-confirm {
            padding: 12px 24px !important;
            font-weight: 600 !important;
            border-radius: 8px !important;
            box-shadow: 0 4px 10px rgba(0, 51, 102, 0.2) !important;
        }
        .swal2-cancel {
            padding: 12px 24px !important;
            font-weight: 600 !important;
            border-radius: 8px !important;
        }
    </style>
</head>
<body>
    <?php include 'includes/header_offcanvas.php'; ?>

    <div class="container" style="padding-top: 3rem; padding-bottom: 3rem; max-width: 800px;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 2rem;">
            <div>
                <h2 style="margin-bottom: 5px;"><?php echo $editMode ? 'Edit Student' : 'Add New Student'; ?></h2>
                <p style="color: var(--text-secondary);">Fill in the details below</p>
            </div>
            <a href="javascript:history.back()" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
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
                <div class="profile-upload-container" style="position: relative; display: inline-block;">
                    <?php 
                    $imgSrc = ($editMode && !empty($student['image_blob'])) 
                        ? "../backend/image.php?type=student&id={$student['id']}" 
                        : "images/male-placeholder.jpg"; 
                    ?>
                    <img src="<?php echo $imgSrc; ?>" style="width: 140px; height: 140px; object-fit: cover; border-radius: 12px; border: 1px solid var(--border-color); box-shadow: var(--shadow-sm);" id="previewImg" onerror="this.src='images/male-placeholder.jpg'">
                </div>
                
                <div style="margin-top: 15px;">
                    <label for="imageUpload" class="btn btn-secondary" style="cursor: pointer;">
                        <i class="fas fa-camera"></i> Upload Photo
                    </label>
                    <input type="file" id="imageUpload" name="image" style="display: none;" accept="image/*" onchange="previewImage(this)">
                </div>

                <?php if ($editMode && !empty($student['image_blob'])): ?>
                    <button type="submit" name="action" value="delete_photo" class="btn" style="background: transparent; border: 1px solid #dc3545; color: #dc3545; font-size: 0.85rem; padding: 6px 12px; margin-top: 10px; border-radius: 6px; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='#dc3545'; this.style.color='white';" onmouseout="this.style.background='transparent'; this.style.color='#dc3545';" formnovalidate onclick="return confirm('Are you sure you want to remove the student\'s photo?');">
                        <i class="fas fa-trash-alt"></i> Remove Photo
                    </button>
                <?php endif; ?>
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

            <button type="button" class="btn btn-primary" style="width: 100%; padding: 14px; margin-top: 10px; font-size: 1.1rem;" onclick="confirmSave(this.form)">
                <i class="fas fa-save"></i> <?php echo $editMode ? 'Update Student Record' : 'Save New Student'; ?>
            </button>
        </form>
    </div>

    <script>
        function confirmSave(form) {
            // Check form validity first
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            const isEdit = <?php echo $editMode ? 'true' : 'false'; ?>;
            const title = isEdit ? 'Update Student Record?' : 'Save New Student?';
            const text = isEdit 
                ? "Are you sure you want to update this student's information?" 
                : "Are you sure you want to add this new student?";
            const confirmBtnText = isEdit ? 'Yes, update it!' : 'Yes, save it!';

            Swal.fire({
                title: title,
                text: text,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: 'var(--bu-blue)',
                cancelButtonColor: '#6c757d',
                confirmButtonText: confirmBtnText,
                cancelButtonText: 'Cancel',
                width: '400px',
                padding: '2em',
                background: '#fff',
                backdrop: `
                    rgba(0,0,0,0.4)
                    left top
                    no-repeat
                `,
                customClass: {
                    popup: 'swal-custom-popup',
                    title: 'swal-custom-title',
                    htmlContainer: 'swal-custom-text',
                    actions: 'swal-custom-actions'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        }

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
