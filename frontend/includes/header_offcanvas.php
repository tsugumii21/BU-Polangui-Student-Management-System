<?php
// Since this is now in frontend/includes/, we must adjust paths if this file includes anything (it doesn't).
// But the links inside it must be relative to the files that INCLUDE this header.
// The files including this are in frontend/ (e.g. frontend/index_admin.php).
// So links to other frontend pages like profile.php are fine as "profile.php".
// Links to backend actions like logout.php need to be "../backend/logout.php".
// Images need to be "images/..." or via backend "image.php" which is now "../backend/image.php".

// We can assume this file is included by files in 'frontend/'.
?>
<nav class="main-header">
    <div class="brand-logo" style="display: flex; align-items: center; gap: 15px;">
        <!-- Sidebar Toggle (Moved here) -->
        <button id="sidebarToggle" class="btn" style="background: transparent; color: white; padding: 5px; font-size: 1.2rem; border: none; cursor: pointer;">
            <i class="fas fa-bars"></i>
        </button>

        <a href="<?php echo ($_SESSION['role'] === 'admin') ? 'index_admin.php' : 'index_user.php'; ?>" style="display: flex; align-items: center; gap: 10px; color: white; text-decoration: none;">
            <img src="images/bup-logo.png" alt="BU Logo">
            <span style="font-weight: 700;">Bicol University Polangui S.M.S</span>
        </a>
    </div>
    
    <div class="nav-links">
        <!-- Search Bar (Modernized) -->
        <div class="search-container">
            <div class="search-input-wrapper">
                <input type="text" id="globalSearch" class="search-input" placeholder="Search students..." autocomplete="off">
                <i class="fas fa-search search-icon-overlay"></i>
            </div>
            <div id="searchResults" class="search-dropdown"></div>
        </div>

        <!-- Role Badge -->
        <span class="badge" style="background: rgba(255,255,255,0.2); padding: 5px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: 600;">
            <?php echo ucfirst($_SESSION['role']); ?>
        </span>

        <!-- Profile & Logout -->
        <a href="profile.php" title="My Profile" style="display: flex; align-items: center; gap: 8px;">
            <img src="../backend/image.php?type=user&id=<?php echo $_SESSION['user_id']; ?>" class="profile-img-small" onerror="this.src='images/male-placeholder.jpg'">
        </a>
        
        <a href="../backend/logout.php" class="btn btn-secondary" style="padding: 8px 16px; font-size: 0.9rem;">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</nav>

<!-- Sidebar (Offcanvas) -->
<div class="offcanvas-overlay" id="overlay"></div>
<div class="offcanvas" id="sidebar">
    <div class="offcanvas-header">
        <h3>Menu</h3>
        <button id="closeSidebar" class="btn" style="background: transparent; color: white;"><i class="fas fa-times"></i></button>
    </div>
    <div class="offcanvas-body">
        <div class="offcanvas-nav">
            <?php if ($_SESSION['role'] === 'admin'): ?>
                <a href="index_admin.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'index_admin.php' ? 'active' : ''; ?>">
                    <i class="fas fa-th-large" style="width: 25px;"></i> Dashboard
                </a>
                <a href="department_selection.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'department_selection.php' ? 'active' : ''; ?>">
                    <i class="fas fa-users" style="width: 25px;"></i> Students Directory
                </a>
                <a href="student_dashboard_admin.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'student_dashboard_admin.php' ? 'active' : ''; ?>">
                    <i class="fas fa-user-plus" style="width: 25px;"></i> Add Student
                </a>
            <?php else: ?>
                <a href="index_user.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'index_user.php' ? 'active' : ''; ?>">
                    <i class="fas fa-th-large" style="width: 25px;"></i> Dashboard
                </a>
                <a href="department_selection.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'department_selection.php' ? 'active' : ''; ?>">
                    <i class="fas fa-users" style="width: 25px;"></i> Students Directory
                </a>
                <a href="student_dashboard_user.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'student_dashboard_user.php' ? 'active' : ''; ?>">
                    <i class="fas fa-user-plus" style="width: 25px;"></i> Add Student
                </a>
            <?php endif; ?>
            <a href="profile.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : ''; ?>">
                <i class="fas fa-user-circle" style="width: 25px;"></i> My Profile
            </a>
        </div>
    </div>
</div>

<script src="js/script.js"></script>
