<?php
include '../config.php';
include '../auth/session.php';
?>
<!DOCTYPE html>
<html data-bs-theme="light">
<head>
    <title>Settings</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <?php include '../dashboard/components/sidebar.php'; ?>

        <!-- Main Content -->
        <div class="content p-4 flex-grow-1">
            <h2>Settings</h2>
            <label for="themeSelect" class="form-label">Theme:</label>
            <select id="themeSelect" class="form-select">
                <option value="light">Light</option>
                <option value="dark">Dark</option>
            </select>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/script.js"></script>
</body>
</html>
