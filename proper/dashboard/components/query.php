<?php
include '../config.php';
include '../auth/session.php';

$userId = $_SESSION['user_id'];
$conn->set_charset("utf8"); // Ensure UTF-8 encoding

/**
 * Execute a SQL query and return the results as an associative array.
 */
function fetchResults($conn, $query, $params = [])
{
    $stmt = $conn->prepare($query);
    if ($params) {
        $stmt->bind_param(str_repeat('i', count($params)), ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Fetch tasks based on the given status.
 */
function fetchTasks($conn, $userId, $statusCondition)
{
    $query = "
        SELECT 
            tasks.id, tasks.title, tasks.status, tasks.position, tasks.completed_at, 
            tasks.parent_task_id, tasks.difficulty_numeric, tasks.difficulty_level, 
            GROUP_CONCAT(tags.name SEPARATOR ',') AS tags
        FROM tasks
        LEFT JOIN task_tags ON tasks.id = task_tags.task_id
        LEFT JOIN tags ON task_tags.tag_id = tags.id
        WHERE $statusCondition 
        AND tasks.user_id = ?  
        GROUP BY tasks.id
        ORDER BY tasks.position IS NULL, tasks.position ASC, tasks.created_at DESC";
    
    return fetchResults($conn, $query, [$userId]);
}

/**
 * Organize tasks into a hierarchical structure (parent -> subtasks).
 */
function organizeTasks($taskResults)
{
    $taskMap = [];
    $tasks = [];

    foreach ($taskResults as &$task) {
        $task['tags'] = $task['tags'] ? explode(',', $task['tags']) : [];
        $task['subtasks'] = [];
        $taskMap[$task['id']] = &$task;
    }

    foreach ($taskMap as &$task) {
        if ($task['parent_task_id'] !== null && isset($taskMap[$task['parent_task_id']])) {
            $taskMap[$task['parent_task_id']]['subtasks'][] = &$task;
        } else {
            $tasks[] = &$task;
        }
    }

    return $tasks;
}

/**
 * Fetch tasks that should appear in "To-Do" (pending tasks + completed parents with pending subtasks).
 */
$todoTasks = fetchTasks($conn, $userId, "tasks.status != 'completed'");
$tasks = organizeTasks($todoTasks);

/**
 * Fetch completed tasks.
 */
$completedTasks = fetchTasks($conn, $userId, "tasks.status = 'completed'");
$completedTasks = organizeTasks($completedTasks);

/**
 * Fetch categories (tags).
 */
$categories = fetchResults($conn, "SELECT * FROM tags ORDER BY name ASC");
?>
