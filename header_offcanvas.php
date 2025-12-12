<?php
// Shared Header and Sidebar Component
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$role = $_SESSION['role'] ?? 'guest';
$username = $_SESSION['username'] ?? 'Guest';
$user_id = $_SESSION['user_id'] ?? 0;
?>

<!-- Offcanvas Overlay -->
<div class="offcanvas-overlay" id="overlay" onclick="toggleSidebar()"></div>

<!-- Offcanvas Sidebar -->
<div class="offcanvas" id="sidebar">
    <div class="offcanvas-header">
        <h3>Menu</h3>
        <button class="btn" onclick="toggleSidebar()" style="background:none; color:white; font-size:1.5rem;">&times;</button>
    </div>
    <div class="offcanvas-body">
        <div class="offcanvas-nav">
            <?php if ($role === 'admin'): ?>
                <a href="index_admin.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'index_admin.php' ? 'active' : ''; ?>">Dashboard</a>
                <a href="students_list_admin.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'students_list_admin.php' ? 'active' : ''; ?>">Students List</a>
                <a href="student_dashboard_admin.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'student_dashboard_admin.php' ? 'active' : ''; ?>">Manage Students</a>
            <?php elseif ($role === 'user'): ?>
                <a href="index_user.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'index_user.php' ? 'active' : ''; ?>">Dashboard</a>
                <a href="students_list_user.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'students_list_user.php' ? 'active' : ''; ?>">Students List</a>
                <a href="student_dashboard_user.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'student_dashboard_user.php' ? 'active' : ''; ?>">Add Student</a>
            <?php endif; ?>
            
            <?php if ($role !== 'guest'): ?>
                <hr>
                <a href="logout.php" style="color: #dc3545;">Logout</a>
            <?php else: ?>
                <a href="login.html">Login</a>
                <a href="signup.html">Sign Up</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Main Header -->
<nav class="main-header">
    <div class="brand-logo">
        <button class="btn" onclick="toggleSidebar()" style="background:none; color:white; font-size:1.2rem; margin-right:10px;">
            <i class="fas fa-bars"></i>
        </button>
        <img src="image/bup-logo.png" alt="Logo">
        <span>Bicol University SMS</span>
    </div>

    <!-- Global Search UI (Only for logged in) -->
    <?php if ($role !== 'guest'): ?>
    <div class="search-container" style="flex:1; max-width:400px; margin:0 20px; display: none;" id="headerSearchBox">
        <!-- Visible on larger screens, hidden on mobile via CSS if needed -->
        <input type="text" id="globalSearch" class="search-input" placeholder="Search students..." onkeyup="handleGlobalSearch(this.value)">
        <div id="globalSearchResults" style="
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            color: black;
            border: 1px solid #ddd;
            border-radius: 0 0 4px 4px;
            max-height: 200px;
            overflow-y: auto;
            z-index: 100;
            display: none;
        "></div>
    </div>
    <script>
        // Show search box via JS to ensure it's functional
        document.getElementById('headerSearchBox').style.display = 'block';
    </script>
    <?php endif; ?>

    <div class="nav-links">
        <?php if ($role !== 'guest'): ?>
            <span style="display: none; @media (min-width: 768px) { display: inline; }">Welcome, <?php echo htmlspecialchars($username); ?></span>
            <a href="profile.php" title="Edit Profile">
                <img src="image.php?type=user&id=<?php echo $user_id; ?>" class="profile-img-small" onerror="this.src='image/male-placeholder.jpg'" alt="Profile">
            </a>
        <?php else: ?>
            <a href="login.html" class="btn btn-secondary">Login</a>
        <?php endif; ?>
    </div>
</nav>
<script src="script.js"></script>

