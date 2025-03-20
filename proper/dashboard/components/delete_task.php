<?php
include '../../config.php';
include '../../auth/session.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "Invalid request"]);
    exit();
}

$userId = $_SESSION['user_id'];

if (!isset($_POST['taskId']) || empty($_POST['taskId'])) {
    echo json_encode(["success" => false, "message" => "Task ID is required"]);
    exit();
}

$taskId = intval($_POST['taskId']);

handleTaskDeletion($conn, $userId, $taskId);

// ========================
// 📌 FUNCTION: Delete Task/Subtask
// ========================
function handleTaskDeletion($conn, $userId, $taskId) {
    // 🔹 Ensure $parentTaskId is defined as NULL
    $parentTaskId = null;

    // 🔹 Check if the task exists and retrieve its parent_task_id
    $stmt = $conn->prepare("SELECT parent_task_id FROM tasks WHERE id = ? AND user_id = ?
");
    $stmt->bind_param("ii", $taskId, $userId);
    $stmt->execute();
    $stmt->bind_result($parentTaskId);
    
    if (!$stmt->fetch()) { // ❗ If no row is found, return an error
        echo json_encode(["success" => false, "message" => "Task not found or you don't have permission to delete it"]);
        $stmt->close();
        exit();
    }
    $stmt->close();

    // 🔹 If parent_task_id is NULL, it's a parent task
    if (is_null($parentTaskId)) { 
        // Delete the parent task and its subtasks
        $stmt = $conn->prepare("DELETE FROM tasks WHERE id = ?");
        $stmt->bind_param("i", $taskId);
    } else {
        // Delete only the subtask
        $stmt = $conn->prepare("DELETE FROM tasks WHERE id = ?");
        $stmt->bind_param("i", $taskId);
    }

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Task deleted successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to delete task"]);
    }

    $stmt->close();
    exit();
}
?>
