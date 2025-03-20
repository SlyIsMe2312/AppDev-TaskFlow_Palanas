<?php
$current_page = basename($_SERVER['PHP_SELF']);
$username = $_SESSION['username'] ?? 'Guest';
?>

<div class="sidebar d-flex flex-column p-3">
    <h3 class="text-center">TaskFlow</h3>

    <!-- Dashboard Section -->
    <span class="nav-title">Dashboard</span>
    <div class="submenu">
        <a href="../dashboard/to-do.php" class="nav-link <?php echo $current_page == 'to-do.php' ? 'active' : ''; ?>">📋 To-Do</a>
        <a href="../dashboard/completed.php" class="nav-link <?php echo $current_page == 'completed.php' ? 'active' : ''; ?>">✅ Completed Tasks</a>
    </div>

    <!-- Categories Section -->
    <span class="nav-title">Manage</span>
    <div class="submenu">
        <a href="../manage/categories.php" class="nav-link <?php echo $current_page == 'categories.php' ? 'active' : ''; ?>">🏷️ Categories</a>
    </div>

    <!-- Settings Section -->
    <div class="submenu">
        <a href="../manage/settings.php" class="nav-link <?php echo $current_page == 'settings.php' ? 'active' : ''; ?>">⚙️ Settings</a>
    </div>

    <!-- Bottom Section -->
    <div class="mt-auto">
        <button id="toggleThemeBtn" class="btn btn-outline-dark w-100">
            <span id="themeIcon">🌙</span> 
        </button>
        <a href="../auth/logout.php" class="btn btn-danger w-100 mt-2">
            <i class="bi bi-box-arrow-right"></i> Logout
        </a>
    </div>
</div>
