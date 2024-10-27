<?php
session_start();

// Include the config file at the beginning
require_once 'config.php';

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
}

function rememberMe($user_id) {
    $token = bin2hex(random_bytes(16));
    $expires = date('Y-m-d H:i:s', strtotime('+30 days'));
    
    global $conn;
    $stmt = $conn->prepare("INSERT INTO remember_tokens (user_id, token, expires) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $user_id, $token, $expires);
    $stmt->execute();
    
    setcookie('remember_token', $token, strtotime('+30 days'), '/', '', true, true);
}

function fetchUpcomingMatches() {
    $api_key = 'YOUR_API_KEY';
    $url = "https://api.example.com/v1/matches/upcoming?api_key={$api_key}";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    if ($response === false) {
        return false;
    }

    $data = json_decode($response, true);
    return $data;
}

function fetchMatchData($match_id) {
    $api_key = 'YOUR_API_KEY';
    $url = "https://api.example.com/v1/matches/{$match_id}?api_key={$api_key}";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    if ($response === false) {
        return false;
    }

    $data = json_decode($response, true);
    return $data;
}


function getProfilePicture($user_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT username, profile_picture FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if ($user && $user['profile_picture']) {
        return $user['profile_picture'];
    } else {
        // Generate placeholder image
        $initials = strtoupper(substr($user['username'], 0, 2));
        $bg_color = sprintf('#%06X', mt_rand(0, 0xFFFFFF));
        return "https://ui-avatars.com/api/?name=" . urlencode($initials) . "&background=" . substr($bg_color, 1) . "&color=ffffff";
    }
}
function checkRememberMe() {
    if (!isset($_COOKIE['remember_token'])) return;
    
    global $conn;
    $token = $_COOKIE['remember_token'];
    $stmt = $conn->prepare("SELECT user_id FROM remember_tokens WHERE token = ? AND expires > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $_SESSION['user_id'] = $row['user_id'];
        // Refresh the token
        rememberMe($row['user_id']);
    }
}

// Call checkRememberMe() at the end of the file
checkRememberMe();