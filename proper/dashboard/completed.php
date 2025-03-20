<?php
include '../config.php';
include '../auth/session.php';
include '../dashboard/components/query.php';
?>
<!DOCTYPE html>
<html data-bs-theme="light">
<head>
    <title>Completed Tasks - TaskFlow</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="d-flex h-100">
    <?php include '../dashboard/components/sidebar.php'; ?>

    <div class="content flex-grow-1 p-3 d-flex flex-column">
        <div class="header-container d-flex justify-content-between align-items-center">
            <h2>Completed Tasks</h2>
        </div>

        <div class="task-view-pane mx-auto mt-4 p-3 rounded">
            <?php if (empty($completedTasks)): ?>
                <p class="text-center">No completed tasks available</p>
            <?php else: ?>
                <ul id="task-list" class="list-group">
                    <?php foreach ($completedTasks as $task): ?>
                        <li class="list-group-item task d-flex flex-column">
                        <span class="difficulty-label" data-difficulty="<?= htmlspecialchars($task['difficulty_numeric']) ?>">
                                <?= htmlspecialchars(number_format($task['difficulty_numeric'], 1)) ?> - 
                                <?= htmlspecialchars($task['difficulty_level']) ?>
                            </span>

                            <div class="d-flex justify-content-between align-items-center task-header">
                                <div class="task-content">
                                    <input type="checkbox" class="task-checkbox" data-task-id="<?= $task['id'] ?>" checked>
                                    <strong><?= htmlspecialchars($task['title']) ?></strong>
                                </div>

                                <?php if (!empty($task['tags'])): ?>
                                    <div class="d-flex flex-wrap taggys">
                                        <?php foreach ($task['tags'] as $tag): ?>
                                            <span class="badge bg-success text-light me-2">
                                                <?= htmlspecialchars($tag) ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <small class="text-muted mt-1">
                                Completed on <?= date("F j, Y", strtotime($task['completed_at'])) ?>
                            </small>

                            <?php if (!empty($task['subtasks'])): ?>
                                <ul class="list-group mt-2">
                                    <?php foreach ($task['subtasks'] as $subtask): ?>
                                        <li class="list-group-item subtask d-flex align-items-center">
                                            <input type="checkbox" class="subtask-checkbox" data-subtask-id="<?= $subtask['id'] ?>" checked>
                                            <span class="subtask-name"><?= htmlspecialchars($subtask['title']) ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </li>

                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/script.js"></script>
</body>
</html>
