<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}
require_once 'config.php';

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Fetch current user data
try {
    $stmt = $pdo->prepare("SELECT username, role FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        // User not found, logout
        header("Location: logout.php");
        exit();
    }
} catch (PDOException $e) {
    die($e->getMessage());
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password']; // Optional
    $confirm_password = $_POST['confirm_password'];
    
    // Image Handling
    $imageBlob = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $imageBlob = file_get_contents($_FILES['image']['tmp_name']);
    }

    if (empty($username)) {
        $error = "Username cannot be empty.";
    } else {
        try {
            // Check if username exists (if changed)
            if ($username !== $user['username']) {
                $check = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
                $check->execute([$username, $user_id]);
                if ($check->fetch()) {
                    $error = "Username already taken.";
                }
            }

            if (!$error) {
                // Update Logic
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
                    // Update without password change
                    if ($imageBlob) {
                        $stmt = $pdo->prepare("UPDATE users SET username = ?, profile_image = ? WHERE id = ?");
                        $stmt->execute([$username, $imageBlob, $user_id]);
                    } else {
                        $stmt = $pdo->prepare("UPDATE users SET username = ? WHERE id = ?");
                        $stmt->execute([$username, $user_id]);
                    }
                    $message = "Profile updated successfully!";
                }

                // Update Session Username if changed
                if (!$error && $message) {
                    $_SESSION['username'] = $username;
                    // Refresh user data
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
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'header_offcanvas.php'; ?>

    <div class="container" style="padding-top: 2rem; max-width: 600px;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 2rem;">
            <h2>My Profile</h2>
            <?php 
            $backLink = ($user['role'] === 'admin') ? 'index_admin.php' : 'index_user.php';
            ?>
            <a href="<?php echo $backLink; ?>" class="btn btn-secondary">Back to Dashboard</a>
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

        <form action="profile.php" method="POST" enctype="multipart/form-data" class="auth-card" style="max-width: 100%;">
            
            <div style="text-align: center; margin-bottom: 20px;">
                <div style="position: relative; display: inline-block;">
                    <img src="image.php?type=user&id=<?php echo $user_id; ?>" style="width: 120px; height: 120px; object-fit: cover; border-radius: 50%; border: 4px solid var(--bu-blue);" onerror="this.src='image/male-placeholder.jpg'">
                    <label for="imageUpload" style="position: absolute; bottom: 0; right: 0; background: var(--bu-orange); color: white; width: 35px; height: 35px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; border: 2px solid white;">
                        <i class="fas fa-camera"></i>
                    </label>
                    <input type="file" id="imageUpload" name="image" style="display: none;" accept="image/*" onchange="previewImage(this)">
                </div>
                <p style="margin-top: 10px; color: #666;">Click camera icon to change photo</p>
            </div>

            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" class="form-control" required value="<?php echo htmlspecialchars($user['username']); ?>">
            </div>

            <div style="border-top: 1px solid #eee; margin: 20px 0; padding-top: 20px;">
                <h4 style="margin-bottom: 15px;">Change Password <small style="font-weight: normal; font-size: 0.8rem; color: #666;">(Leave blank to keep current)</small></h4>
                
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" name="password" class="form-control" minlength="6">
                </div>

                <div class="form-group">
                    <label>Confirm New Password</label>
                    <input type="password" name="confirm_password" class="form-control">
                </div>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%;">Update Profile</button>
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

