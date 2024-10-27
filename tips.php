<?php
require_once 'config.php';
require_once 'functions.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get the current time
$current_time = new DateTime();

// Fetch upcoming and ongoing matches
$query = "SELECT t.*, m.commence_time, m.status 
          FROM tips t 
          JOIN matches m ON t.match_name = m.match_name 
          WHERE m.status IN ('upcoming', 'ongoing') 
          ORDER BY m.commence_time ASC";

$stmt = $conn->prepare($query);

if ($stmt === false) {
    die("Prepare failed: " . $conn->error);
}

$execute_result = $stmt->execute();

if ($execute_result === false) {
    die("Execute failed: " . $stmt->error);
}

$result = $stmt->get_result();

$tips = [];
while ($row = $result->fetch_assoc()) {
    $tips[] = $row;
}

// Delete completed matches
$delete_query = "DELETE t FROM tips t 
                 JOIN matches m ON t.match_name = m.match_name 
                 WHERE m.status = 'completed'";
$conn->query($delete_query);

include 'header.php';
?>

<h2>Betting Tips</h2>

<?php if (empty($tips)): ?>
    <p>No tips available at the moment.</p>
<?php else: ?>
    <?php foreach ($tips as $tip): 
        $commence_time = new DateTime($tip['commence_time']);
        $time_diff = $current_time->diff($commence_time);
    ?>
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title"><?php echo htmlspecialchars($tip['match_name']); ?></h5>
                <p class="card-text">
                    Match starts: <?php echo $commence_time->format('Y-m-d H:i'); ?>
                    <?php if ($tip['status'] == 'upcoming'): ?>
                        <?php echo $time_diff->days > 0 ? $time_diff->days . ' days, ' : ''; ?>
                        <?php echo $time_diff->h . ' hours, ' . $time_diff->i . ' minutes'; ?>
                    <?php elseif ($tip['status'] == 'ongoing'): ?>
                        <span class="text-danger">Match is ongoing</span>
                    <?php endif; ?>
                </p>
                
                <h6>Tips:</h6>
                <ul>
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
                    foreach (json_decode($tip['tips'], true) as $single_tip): 
                    ?>
                        <li>
                            <?php
                            echo isset($tip_labels[$single_tip]) ? htmlspecialchars($tip_labels[$single_tip]) : htmlspecialchars($single_tip);
                            ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
                
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php include 'footer.php'; ?>