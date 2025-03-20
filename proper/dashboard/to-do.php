<?php
include '../config.php';
include '../auth/session.php';
include '../dashboard/components/query.php';
?>
<!DOCTYPE html>
<html data-bs-theme="light">
<head>
    <title>TaskFlow Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="d-flex h-100">
    <?php include '../dashboard/components/sidebar.php'; ?>

    <div class="content flex-grow-1 p-3 d-flex flex-column">
        <div class="header-container d-flex justify-content-between align-items-center">
            <h2>My Tasks</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTaskModal">+ Add Task</button>
        </div>

        <div class="task-view-pane mx-auto mt-4 p-3 rounded">
            <?php if (empty($tasks)): ?>
                <p class="text-center">No tasks available</p>
            <?php else: ?>
                <ul class="list-group" id="task-list">
                    <?php foreach ($tasks as $task): ?>
                        <li class="list-group-item task d-flex flex-column" draggable="true" data-task-id="<?= $task['id'] ?>">
                            <span class="difficulty-label" data-difficulty="<?= htmlspecialchars($task['difficulty_numeric']) ?>">
                                <?= htmlspecialchars(number_format($task['difficulty_numeric'], 1)) ?> - 
                                <?= htmlspecialchars($task['difficulty_level']) ?>
                            </span>

                            <div class="d-flex justify-content-between align-items-center task-header">
                                <div class="task-content">
                                    <input type="checkbox" class="task-checkbox" data-task-id="<?= $task['id'] ?>"
                                        <?= $task['status'] === 'completed' ? 'checked' : '' ?>>
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

                                <button class="delete-task-btn" data-task-id="<?= $task['id'] ?>"><i class="bi bi-trash"></i></button>
                            </div>

                            <!-- Add Subtask Button -->
                            <button class="add-subtask-btn" data-task-id="<?= $task['id'] ?>">+</button>

                            <?php if (!empty($task['subtasks'])): ?>
                                <ul class="list-group subtasks">
                                    <?php foreach ($task['subtasks'] as $subtask): ?>
                                        <li class="list-group-item subtask d-flex align-items-center">
                                            <input type="checkbox" class="subtask-checkbox" data-subtask-id="<?= $subtask['id'] ?>"
                                                <?= ($subtask['status'] === 'completed') ? 'checked' : '' ?>>
                                            <span class="subtask-name"><?= htmlspecialchars($subtask['title']) ?></span>
                                            <button class="delete-subtask-btn" data-subtask-id="<?= $subtask['id'] ?>"><i class="bi bi-trash"></i></button>
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



<!-- Add Task Modal -->
<?php include '../dashboard/components/modals.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/script.js"></script>
</body>
</html>
