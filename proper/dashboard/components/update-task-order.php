<?php
include '../../config.php';  // Ensure DB connection is set up
header('Content-Type: application/json'); // Force JSON response

try {
    // Read JSON input
    $input = file_get_contents("php://input");
    $data = json_decode($input, true);

    if (!isset($data) || !is_array($data)) {
        throw new Exception("Invalid input data");
    }

    // Prepare the SQL statement outside the loop for efficiency
    $stmt = $conn->prepare("UPDATE tasks SET position = ? WHERE id = ?");

    // Execute the update for each task
    foreach ($data as $task) {
        if (!isset($task['task_id'], $task['position'])) {
            continue; // Skip invalid entries
        }

        $taskId = intval($task['task_id']);
        $position = intval($task['position']);

        $stmt->bind_param("ii", $position, $taskId);
        $stmt->execute();
    }

    $stmt->close();
    $conn->close();

    echo json_encode(["success" => true, "message" => "Task order updated successfully"]);
} catch (Exception $e) {
    http_response_code(400); // Bad request
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>
