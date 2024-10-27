<?php
require_once 'config.php';
require_once 'functions.php';

// Fetch ongoing and recently completed matches
$matches = $conn->query("SELECT * FROM matches WHERE status IN ('ongoing', 'completed') AND date >= DATE_SUB(NOW(), INTERVAL 3 DAY)");

while ($match = $matches->fetch_assoc()) {
    // Fetch the latest match data from an API (you'll need to implement this function)
    $latest_data = fetchMatchData($match['id']);

    if ($latest_data['status'] == 'completed' && $match['status'] != 'completed') {
        // Update match status and result
        $stmt = $conn->prepare("UPDATE matches SET status = 'completed', result = ? WHERE id = ?");
        $stmt->bind_param("si", $latest_data['result'], $match['id']);
        $stmt->execute();

        // Update tips for this match
        $tips = $conn->query("SELECT * FROM tips WHERE match_id = " . $match['id']);
        while ($tip = $tips->fetch_assoc()) {
            $correct_tips = [];
            foreach (json_decode($tip['tips']) as $single_tip) {
                if (checkTipCorrectness($single_tip, $latest_data['result'])) {
                    $correct_tips[] = $single_tip;
                }
            }
            $stmt = $conn->prepare("UPDATE tips SET correct_tips = ? WHERE id = ?");
            $stmt->bind_param("si", json_encode($correct_tips), $tip['id']);
            $stmt->execute();
        }
    } elseif ($latest_data['status'] == 'ongoing' && $match['status'] != 'ongoing') {
        // Update match status to ongoing
        $stmt = $conn->prepare("UPDATE matches SET status = 'ongoing' WHERE id = ?");
        $stmt->bind_param("i", $match['id']);
        $stmt->execute();
    }
}

function checkTipCorrectness($tip, $result) {
    // Implement the logic to check if a tip is correct based on the match result
    // This will depend on your specific betting types and result format
    // Return true if the tip is correct, false otherwise
}