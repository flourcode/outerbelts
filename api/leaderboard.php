<?php
// Set headers to allow cross-origin requests and specify JSON response
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// File to store leaderboard data
$leaderboardFile = "leaderboard.json";

// Function to read leaderboard data
function getLeaderboard() {
    global $leaderboardFile;
    if (file_exists($leaderboardFile)) {
        $data = file_get_contents($leaderboardFile);
        return json_decode($data, true) ?: [];
    }
    return [];
}

// Function to save leaderboard data
function saveLeaderboard($scores) {
    global $leaderboardFile;
    file_put_contents($leaderboardFile, json_encode($scores));
}

// Handle GET request - retrieve scores
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $scores = getLeaderboard();
    echo json_encode($scores);
}

// Handle POST request - add new score
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get posted data
    $data = json_decode(file_get_contents("php://input"), true);
    
    // Validate data
    if (!isset($data['initials']) || !isset($data['score'])) {
        http_response_code(400);
        echo json_encode(["message" => "Missing required fields"]);
        exit();
    }
    
    // Sanitize input
    $initials = substr(preg_replace("/[^A-Z]/", "", strtoupper($data['initials'])), 0, 3);
    $score = intval($data['score']);
    
    if (empty($initials)) {
        $initials = "AAA";
    }
    
    // Get existing scores
    $scores = getLeaderboard();
    
    // Add new score
    $scores[] = [
        "initials" => $initials,
        "score" => $score,
        "date" => date("Y-m-d H:i:s")
    ];
    
    // Sort by score (descending)
    usort($scores, function($a, $b) {
        return $b['score'] - $a['score'];
    });
    
    // Keep only top 10
    $scores = array_slice($scores, 0, 10);
    
    // Save updated leaderboard
    saveLeaderboard($scores);
    
    // Return success
    echo json_encode(["success" => true, "scores" => $scores]);
}
?>