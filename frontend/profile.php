<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}
require_once '../database/config.php';

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Fetch current user data
try {
    $stmt = $pdo->prepare("SELECT username, role FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        header("Location: logout.php");
        exit();
    }
} catch (PDOException $e) {
    die($e->getMessage());
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password']; 
    $confirm_password = $_POST['confirm_password'];
    
    $imageBlob = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $imageBlob = file_get_contents($_FILES['image']['tmp_name']);
    }

    if (empty($username)) {
        $error = "Username cannot be empty.";
    } else {
        try {
            if ($username !== $user['username']) {
                $check = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
                $check->execute([$username, $user_id]);
                if ($check->fetch()) {
                    $error = "Username already taken.";
                }
            }

            if (!$error) {
                if (!empty($password)) {
                    if ($password !== $confirm_password) {
                        $error = "Passwords do not match.";
                    } else {
                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                        if ($imageBlob) {
                            $stmt = $pdo->prepare("UPDATE users SET username = ?, password = ?, profile_image = ? WHERE id = ?");
                            $stmt->execute([$username, $hashed_password, $imageBlob, $user_id]);
                        } else {
                            $stmt = $pdo->prepare("UPDATE users SET username = ?, password = ? WHERE id = ?");
                            $stmt->execute([$username, $hashed_password, $user_id]);
                        }
                        $message = "Profile updated successfully!";
                    }
                } else {
                    if ($imageBlob) {
                        $stmt = $pdo->prepare("UPDATE users SET username = ?, profile_image = ? WHERE id = ?");
                        $stmt->execute([$username, $imageBlob, $user_id]);
                    } else {
                        $stmt = $pdo->prepare("UPDATE users SET username = ? WHERE id = ?");
                        $stmt->execute([$username, $user_id]);
                    }
                    $message = "Profile updated successfully!";
                }

                if (!$error && $message) {
                    $_SESSION['username'] = $username;
                    $user['username'] = $username;
                }
            }
        } catch (PDOException $e) {
            $error = "Database Error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile | BU SMS</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header_offcanvas.php'; ?>

    <div class="container" style="padding-top: 3rem; max-width: 700px;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 2rem;">
            <div>
                <h2 style="margin-bottom: 5px;">My Profile</h2>
                <p style="color: var(--text-secondary);">Update your account settings</p>
            </div>
            <?php 
            $backLink = ($user['role'] === 'admin') ? 'index_admin.php' : 'index_user.php';
            ?>
            <a href="<?php echo $backLink; ?>" class="btn btn-secondary">Back to Dashboard</a>
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

        <form action="profile.php" method="POST" enctype="multipart/form-data" class="auth-card" style="max-width: 100%;">
            
            <div style="text-align: center; margin-bottom: 30px; position: relative;">
                <div class="profile-upload-container" style="position: relative; display: inline-block; cursor: pointer;">
                    <img src="../backend/image.php?type=user&id=<?php echo $user_id; ?>" style="width: 140px; height: 140px; object-fit: cover; border-radius: 50%; border: 5px solid var(--bu-blue); box-shadow: 0 5px 15px rgba(0,0,0,0.15);" onerror="this.src='images/male-placeholder.jpg'">
                    <label for="imageUpload" class="profile-upload-overlay" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); border-radius: 50%; display: flex; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.3s; color: white;">
                        <i class="fas fa-camera fa-2x"></i>
                    </label>
                    <input type="file" id="imageUpload" name="image" style="display: none;" accept="image/*" onchange="previewImage(this)">
                </div>
                <p style="margin-top: 15px; color: var(--text-secondary); font-size: 0.9rem;">Tap image to change photo</p>
            </div>

            <div class="form-group">
                <label>Username</label>
                <div style="position: relative;">
                    <i class="fas fa-user" style="position: absolute; left: 15px; top: 14px; color: #aaa;"></i>
                    <input type="text" name="username" class="form-control" required value="<?php echo htmlspecialchars($user['username']); ?>" style="padding-left: 40px;">
                </div>
            </div>

            <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 25px 0; border: 1px solid var(--border-color);">
                <h4 style="margin-bottom: 15px; font-size: 1.1rem; display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-lock" style="color: var(--bu-orange);"></i> Security 
                    <span style="font-weight: normal; font-size: 0.85rem; color: #666; margin-left: auto;">Leave blank to keep current</span>
                </h4>
                
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" name="password" class="form-control" minlength="6" placeholder="Enter new password">
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label>Confirm New Password</label>
                    <input type="password" name="confirm_password" class="form-control" placeholder="Repeat new password">
                </div>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 14px; font-size: 1.1rem;">Save Changes</button>
        </form>
    </div>

    <script>
        function previewImage(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    input.previousElementSibling.previousElementSibling.src = e.target.result;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>
</html>
