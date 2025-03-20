<?php
include '../config.php';
include '../auth/session.php';

// Fetch tasks with their subtasks
$stmt = $conn->prepare("SELECT t.id, t.title, t.status, t.created_at,
               GROUP_CONCAT(st.id, ':', st.title, ':', st.status SEPARATOR '|') AS subtasks
        FROM tasks t
        LEFT JOIN tasks st ON t.id = st.parent_task_id  -- Self-join for subtasks
        WHERE t.user_id = ?
        GROUP BY t.id");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

$tasks = [];
while ($row = $result->fetch_assoc()) {
    $row['subtasks'] = $row['subtasks'] 
    ? array_map(function ($sub) {
        $parts = explode(':', $sub);
        return count($parts) === 3 ? array_combine(['id', 'title', 'status'], $parts) : null;
    }, explode('|', $row['subtasks'])) 
    : [];

    // Remove null values if any
    $row['subtasks'] = array_filter($row['subtasks']);

    $tasks[] = $row;
}
$stmt->close();

// Fetch tags/categories
$stmtTags = $conn->prepare("SELECT * FROM tags WHERE user_id = ? ORDER BY id DESC");
$stmtTags->bind_param("i", $_SESSION['user_id']);
$stmtTags->execute();
$resultTags = $stmtTags->get_result();
$tags = $resultTags->fetch_all(MYSQLI_ASSOC);
$stmtTags->close();
?>
<!DOCTYPE html>
<html data-bs-theme="light">
<head>
    <title>TaskFlow Categories</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="d-flex h-100">
    <!-- Sidebar -->
    <?php include '../dashboard/components/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="content flex-grow-1 p-3 d-flex flex-column">
        <div class="header-container d-flex justify-content-between align-items-center" style="width: 90%; margin: 0 auto;">
            <h2>Categories</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">+ Add Category</button>
        </div>

        <!-- Categories View Pane -->
        <div class="task-view-pane mx-auto mt-4 p-3 rounded" style="width: 90%; min-height: 80vh; overflow-y: auto;">
            <?php if (empty($tags)): ?>
                <p class="text-center">No categories available</p>
            <?php else: ?>
                <ul class="list-group">
                    <?php foreach ($tags as $tag): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <?= htmlspecialchars($tag['name']) ?>
                            <span class="badge bg-secondary">Tag ID: <?= $tag['id'] ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../dashboard/components/modals.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/script.js"></script>
</body>
</html>
