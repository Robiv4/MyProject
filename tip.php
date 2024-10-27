<?php
require_once 'config.php';
require_once 'functions.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get the tip ID from the URL
$tip_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($tip_id === 0) {
    die("Invalid tip ID");
}

// Fetch the tip details
$query = "SELECT t.*, m.home_team, m.away_team, m.commence_time, m.status
          FROM tips t
          JOIN matches m ON t.match_name = m.match_name
          WHERE t.id = ?";

$stmt = $conn->prepare($query);

if ($stmt === false) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("i", $tip_id);

if (!$stmt->execute()) {
    die("Execute failed: " . $stmt->error);
}

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Tip not found");
}

$tip = $result->fetch_assoc();

// Get the current time
$current_time = new DateTime();
$commence_time = new DateTime($tip['commence_time']);
$time_diff = $current_time->diff($commence_time);

include 'header.php';
?>

<div class="container mt-5">
    <h1 class="mb-4">Match Tip: <?php echo htmlspecialchars($tip['home_team'] . ' vs ' . $tip['away_team']); ?></h1>
    
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Match Details</h5>
            <p class="card-text">
                <strong>Date:</strong> <?php echo $commence_time->format('Y-m-d'); ?><br>
                <strong>Time:</strong> <?php echo $commence_time->format('H:i'); ?><br>
                <strong>Status:</strong> 
                <?php 
                if ($tip['status'] == 'upcoming') {
                    echo 'Upcoming (Starts in ' . $time_diff->days . ' days, ' . $time_diff->h . ' hours, ' . $time_diff->i . ' minutes)';
                } elseif ($tip['status'] == 'ongoing') {
                    echo '<span class="text-danger">Match is ongoing</span>';
                } else {
                    echo htmlspecialchars($tip['status']);
                }
                ?>
            </p>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-body">
            <h5 class="card-title">Betting Tips</h5>
            <ul class="list-group list-group-flush">
                <?php 
                $tip_labels = [
                    '1' => 'Home Win (1)',
                    'X' => 'Draw (X)',
                    '2' => 'Away Win (2)',
                    '1X' => 'Home Win or Draw (1X)',
                    '12' => 'Home or Away Win (12)',
                    'X2' => 'Draw or Away Win (X2)',
                    'BTTS' => 'Both Teams To Score',
                    'O2.5' => 'Over 2.5 Goals',
                    'U2.5' => 'Under 2.5 Goals'
                ];
                $tips_array = json_decode($tip['tips'], true);
                foreach ($tips_array as $single_tip): 
                ?>
                    <li class="list-group-item">
                        <?php echo htmlspecialchars($tip_labels[$single_tip] ?? $single_tip); ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-body">
            <h5 class="card-title">Odds</h5>
            <?php 
            $odds_array = json_decode($tip['odds'], true);
            foreach ($odds_array as $key => $value):
            ?>
                <p class="card-text">
                    <strong><?php echo htmlspecialchars($tip_labels[$key] ?? $key); ?>:</strong> <?php echo htmlspecialchars($value); ?>
                </p>
            <?php endforeach; ?>
        </div>
    </div>

    <?php if (isset($tip['goals'])): ?>
    <div class="card mt-4">
        <div class="card-body">
            <h5 class="card-title">Predicted Score</h5>
            <?php 
            $goals_array = json_decode($tip['goals'], true);
            ?>
            <p class="card-text">
                <?php echo htmlspecialchars($tip['home_team']); ?>: <?php echo htmlspecialchars($goals_array['home']); ?> - 
                <?php echo htmlspecialchars($tip['away_team']); ?>: <?php echo htmlspecialchars($goals_array['away']); ?>
            </p>
        </div>
    </div>
    <?php endif; ?>

    <a href="index.php" class="btn btn-primary mt-4">Back to Home</a>
</div>

<?php include 'footer.php'; ?>