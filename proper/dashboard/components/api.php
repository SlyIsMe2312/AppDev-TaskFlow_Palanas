<?php
include '../../config.php';
include '../../auth/session.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "User session missing"]);
    exit();
}

$response = ["success" => false];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $taskTitle = $_POST["taskTitle"] ?? null;
    $subtaskTitle = $_POST["subtaskTitle"] ?? null;
    $aiGenerate = isset($_POST["aiGenerate"]) ? filter_var($_POST["aiGenerate"], FILTER_VALIDATE_BOOLEAN) : false;
    $numSubtasks = $_POST["numSubtasks"] ?? 1;
    $parentTaskId = $_POST["parentTaskId"] ?? null;

    $userId = $_SESSION["user_id"] ?? null;
    $categoriesList = getUserCategories($userId);

    if (!$categoriesList) {
        echo json_encode(["success" => false, "message" => "User categories are required."]);
        exit();
    }

    // **CASE 1: Creating a Parent Task**
    if (!$parentTaskId) {
        $aiPrompt = "Analyze the task: \"$taskTitle\". Follow these steps:
        - Assign a difficulty numeric value (0.1 - 10.0), with easy (0.0-4.0), medium (4.1-7.5), and hard (7.6-10.0).
        - Use only the user's provided categories: [$categoriesList].
        - Return JSON in this format:
        {
            \"taskTitle\": \"$taskTitle\",
            \"difficulty_numeric\": X.X,
            \"tags\": [\"tag1\", \"tag2\", \"...\"] 
        }";

        $aiData = callAI($aiPrompt);

        if ($aiData) {
            $response = [
                "success" => true,
                "taskTitle" => $aiData["taskTitle"] ?? $taskTitle,
                "difficulty_numeric" => $aiData["difficulty_numeric"] ?? 1.0,
                "tags" => $aiData["tags"] ?? []
            ];
        } else {
            $response["message"] = "AI generation failed.";
        }
    }

    // **CASE 2: AI Generate ON (Subtasks)**
    elseif ($aiGenerate) {
        $aiPrompt = "Analyze the subtask: \"$subtaskTitle\" (part of \"$taskTitle\").  
        Generate exactly $numSubtasks subtasks with:
        - A clear and actionable title.
        - Difficulty numeric value (0.1 - 10.0).
        - Tags selected only from: [$categoriesList].
        - Return JSON in this format:
        {
            \"subtasks\": [
                {
                    \"title\": \"Subtask 1 title\",
                    \"difficulty_numeric\": X.X,
                    \"tags\": [\"tag1\", \"tag2\"]
                }
            ]
        }";

        $aiData = callAI($aiPrompt);

        if ($aiData && isset($aiData["subtasks"])) {
            $response = [
                "success" => true,
                "subtasks" => $aiData["subtasks"]
            ];
        } else {
            $response["message"] = "AI subtask generation failed.";
        }
    }

    // **CASE 3: AI Generate OFF (Subtask)**
    else {
        if (!$subtaskTitle) {
            echo json_encode(["error" => "Subtask title is missing."]);
            exit();
        }

        $aiPrompt = "Analyze the subtask: \"$subtaskTitle\".
        - DO NOT change the title.
        - Assign a difficulty numeric value (0.1 - 10.0).
        - Use only the user's provided categories: [$categoriesList].
        - Return JSON in this format:
        {
            \"subtasks\": [
                {\"title\": \"$subtaskTitle\", \"difficulty_numeric\": X.X, \"tags\": [\"tag1\", \"tag2\"] }
            ]
        }";

        $aiData = callAI($aiPrompt);

        if ($aiData && isset($aiData["subtasks"])) {
            $response = [
                "success" => true,
                "subtasks" => $aiData["subtasks"]
            ];
        } else {
            $response["message"] = "AI subtask analysis failed.";
        }
    }
}

echo json_encode($response);
exit();

/**
 * Calls AI API
 */
function callAI($prompt) {
    $apiKey = 'AIzaSyAtY13_tfNpA51u29rvHN7vdHH8irHUzAQ';
    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash-thinking-exp-01-21:generateContent?key=$apiKey";

    $data = [
        'contents' => [
            [
                'parts' => [
                    ['text' => $prompt]
                ]
            ]
        ]
    ];

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json']
    ]);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        error_log("Curl error: " . curl_error($ch));
        return null;
    }

    curl_close($ch);

    if (!$response) {
        error_log("Failed to get response from AI.");
        return null;
    }

    $responseData = json_decode($response, true);

    if (!isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
        error_log("AI response format incorrect: " . $response);
        return null;
    }

    $rawText = $responseData['candidates'][0]['content']['parts'][0]['text'];
    $cleanText = trim(preg_replace('/^```json\s*|\s*```$/', '', $rawText));

    return json_decode($cleanText, true);
}

/**
 * Fetch user categories
 */
function getUserCategories($userId) {
    global $conn;

    $stmt = $conn->prepare("SELECT name FROM tags WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $categories = [];

    while ($row = $result->fetch_assoc()) {
        $categories[] = $row["name"];
    }

    return empty($categories) ? "none" : implode(", ", $categories);
}
?>
