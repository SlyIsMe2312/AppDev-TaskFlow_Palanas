<?php
require '../../config.php'; // Database connection

// Get status filter from URL (default: all tasks)
$status = isset($_GET['status']) ? $_GET['status'] : null;

// Prepare SQL query based on status
if ($status === 'pending') {
    $query = "SELECT * FROM tasks WHERE status != 'completed' ORDER BY position ASC";
} elseif ($status === 'completed') {
    $query = "SELECT * FROM tasks WHERE status = 'completed' ORDER BY position ASC";
} else {
    $query = "SELECT * FROM tasks ORDER BY position ASC"; // Default: fetch all tasks
}

$result = $conn->query($query);
$tasks = [];

if ($result->num_rows > 0) {
    while ($task = $result->fetch_assoc()) {
        $task['tags'] = !empty($task['tags']) ? explode(",", $task['tags']) : []; // Convert tags to array
        $tasks[] = $task;
    }
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($tasks);

$conn->close();
?>
