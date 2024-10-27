<?php
function fetchMatchesForCurrentWeek() {
    $api_key = '462697d3e8374ce29d67979825611e3c'; // Cseréld ki a saját API kulcsoddal
    $start_date = date('Y-m-d');
    $end_date = date('Y-m-d', strtotime('+7 days'));
    
    $url = "http://api.football-data.org/v4/matches?dateFrom={$start_date}&dateTo={$end_date}";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "X-Auth-Token: {$api_key}"
    ));
    $response = curl_exec($ch);
    curl_close($ch);

    if ($response === false) {
        return array();
    }

    $data = json_decode($response, true);
    
    if (!isset($data['matches']) || !is_array($data['matches'])) {
        return array();
    }

    $formatted_matches = array_map(function($match) {
        return array(
            'id' => $match['id'],
            'home_team' => $match['homeTeam']['name'],
            'away_team' => $match['awayTeam']['name'],
            'commence_time' => $match['utcDate'],
            'odds' => array(
                '1' => $match['odds']['homeWin'] ?? null,
                'X' => $match['odds']['draw'] ?? null,
                '2' => $match['odds']['awayWin'] ?? null
            )
        );
    }, $data['matches']);

    return $formatted_matches;
}

function fetchMatchDataFromAPI($match_id) {
    $api_key = '462697d3e8374ce29d67979825611e3c'; // Cseréld ki a saját API kulcsoddal
    $url = "http://api.football-data.org/v4/matches/{$match_id}";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "X-Auth-Token: {$api_key}"
    ));
    $response = curl_exec($ch);
    curl_close($ch);

    if ($response === false) {
        return null;
    }

    $data = json_decode($response, true);
    
    if (!isset($data['match'])) {
        return null;
    }

    return array(
        'id' => $data['match']['id'],
        'home_team' => $data['match']['homeTeam']['name'],
        'away_team' => $data['match']['awayTeam']['name'],
        'commence_time' => $data['match']['utcDate'],
        'odds' => array(
            '1' => $data['match']['odds']['homeWin'] ?? null,
            'X' => $data['match']['odds']['draw'] ?? null,
            '2' => $data['match']['odds']['awayWin'] ?? null
        )
    );
}