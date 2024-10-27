<?php
require_once 'config.php';
require_once 'functions.php';
require_once 'api_functions.php';

if (!isAdmin()) {
    header("Location: index.php");
    exit();
}

// Fetch matches for the current week from the API
$current_week_matches = fetchMatchesForCurrentWeek();

// Handle API errors
if (isset($current_week_matches['error'])) {
    $api_error = $current_week_matches['error'];
    $current_week_matches = [];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['create_tip'])) {
        $match_name = $_POST['match_name'];
        $tips = isset($_POST['tips']) ? $_POST['tips'] : array();
        $odds = $_POST['odds'];
        $home_goals = $_POST['home_goals'];
        $away_goals = $_POST['away_goals'];

        $tips_json = json_encode($tips);
        $goals_json = json_encode(['home' => $home_goals, 'away' => $away_goals]);

        try {
            $match_data = explode(' vs ', $match_name);
            $home_team = trim($match_data[0]);
            $away_team = trim($match_data[1]);
            $commence_time = $_POST['commence_time'];

            $stmt = $conn->prepare("INSERT INTO matches (match_name, home_team, away_team, commence_time) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE home_team = VALUES(home_team), away_team = VALUES(away_team), commence_time = VALUES(commence_time)");
            $stmt->bind_param("ssss", $match_name, $home_team, $away_team, $commence_time);
            $stmt->execute();

            $stmt = $conn->prepare("INSERT INTO tips (match_name, tips, odds, goals) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $match_name, $tips_json, $odds, $goals_json);
            $stmt->execute();
            
            $success = "Tip added successfully!";
        } catch (Exception $e) {
            $error = "Error adding tip: " . $e->getMessage();
        }
    }
}

// Fetch all tips
$tips_result = $conn->query("SELECT t.*, m.commence_time FROM tips t JOIN matches m ON t.match_name = m.match_name ORDER BY m.commence_time DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Manage Tips</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>Admin - Manage Tips</h2>

    <?php if (isset($success)): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if (isset($api_error)): ?>
        <div class="alert alert-warning">
            <?php echo htmlspecialchars($api_error); ?>
            <br>
            Kérjük, ellenőrizze a következőket:
            <ul>
                <li>Az API kulcs helyesen van beállítva az api_functions.php fájlban</li>
                <li>A sport, regions és markets paraméterek megfelelőek</li>
                <li>Van aktív internetkapcsolat a szerveren</li>
                <li>Az API szolgáltató szervere elérhető</li>
            </ul>
        </div>
    <?php endif; ?>

    <h3>Create New Tip</h3>
    <form method="post" action="">
        <div class="mb-3">
            <label for="match_name" class="form-label">Select Match (This Week)</label>
            <select class="form-control" id="match_name" name="match_name" required <?php echo empty($current_week_matches) ? 'disabled' : ''; ?>>
                <?php if (!empty($current_week_matches)): ?>
                    <?php foreach ($current_week_matches as $match): ?>
                        <?php $match_name = $match['home_team'] . ' vs ' . $match['away_team']; ?>
                        <option value="<?php echo htmlspecialchars($match_name); ?>" 
                                data-odds='<?php echo htmlspecialchars(json_encode($match['odds'])); ?>' 
                                data-commence-time="<?php echo htmlspecialchars($match['commence_time']); ?>">
                            <?php echo htmlspecialchars($match_name . ' - ' . date('Y-m-d H:i', strtotime($match['commence_time']))); ?>
                        </option>
                    <?php endforeach; ?>
                <?php else: ?>
                    <option value="">Nem sikerült betölteni a meccseket</option>
                <?php endif; ?>
            </select>
        </div>
        <input type="hidden" id="commence_time" name="commence_time">
        <div class="mb-3">
            <label class="form-label">Betting Tips</label>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="tips[]" value="1" id="tip1">
                <label class="form-check-label" for="tip1">Home Win (1)</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="tips[]" value="X" id="tipX">
                <label class="form-check-label" for="tipX">Draw (X)</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="tips[]" value="2" id="tip2">
                <label class="form-check-label" for="tip2">Away Win (2)</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="tips[]" value="1X" id="tip1X">
                <label class="form-check-label" for="tip1X">Home Win or Draw (1X)</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="tips[]" value="12" id="tip12">
                <label class="form-check-label" for="tip12">Home or Away Win (12)</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="tips[]" value="X2" id="tipX2">
                <label class="form-check-label" for="tipX2">Draw or Away Win (X2)</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="tips[]" value="BTTS" id="tipBTTS">
                <label class="form-check-label" for="tipBTTS">Both Teams To Score</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="tips[]" value="O2.5" id="tipO25">
                <label class="form-check-label" for="tipO25">Over 2.5 Goals</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="tips[]" value="U2.5" id="tipU25">
                <label class="form-check-label" for="tipU25">Under 2.5 Goals</label>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">Predicted Score</label>
            <div class="row">
                <div class="col">
                    <input type="number" class="form-control" name="home_goals" placeholder="Home Goals" min="0">
                </div>
                <div class="col">
                    <input type="number" class="form-control" name="away_goals" placeholder="Away Goals" min="0">
                </div>
            </div>
        </div>
        <div class="mb-3">
            <label for="odds" class="form-label">Odds</label>
            <input type="hidden" id="odds" name="odds">
            <div id="odds_display"></div>
        </div>
        <button type="submit" name="create_tip" class="btn btn-primary" <?php echo empty($current_week_matches) ? 'disabled' : ''; ?>>Create Tip</button>
    </form>

    <h3 class="mt-5">Existing Tips</h3>
    <table class="table">
        <thead>
            <tr>
                <th>Match</th>
                <th>Tips</th>
                <th>Predicted Score</th>
                <th>Odds</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($tip = $tips_result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($tip['match_name']); ?><br>
                        <small><?php echo date('Y-m-d H:i', strtotime($tip['commence_time'])); ?></small>
                    </td>
                    <td>
                        <?php 
                        $tips = json_decode($tip['tips'], true);
                        echo implode(', ', $tips);
                        ?>
                    </td>
                    <td>
                        <?php 
                        $goals = json_decode($tip['goals'], true);
                        echo $goals['home'] . ' - ' . $goals['away'];
                        ?>
                    </td>
                    <td><?php echo htmlspecialchars($tip['odds']); ?></td>
                    <td>
                        <form method="post" action="" class="d-inline">
                            <input type="hidden" name="delete_tip" value="<?php echo $tip['id']; ?>">
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this tip?')">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<script>
document.getElementById('match_name').addEventListener('change', function() {
    var selectedOption = this.options[this.selectedIndex];
    var odds = JSON.parse(selectedOption.getAttribute('data-odds'));
    document.getElementById('odds').value = JSON.stringify(odds);
    var oddsDisplay = '';
    for (var key in odds) {
        oddsDisplay += key + ': ' + odds[key] + '<br>';
    }
    document.getElementById('odds_display').innerHTML = oddsDisplay;
    document.getElementById('commence_time').value = selectedOption.getAttribute('data-commence-time');
});

// Trigger the change event on page load to set initial odds
if (document.getElementById('match_name').options.length > 0) {
    document.getElementById('match_name').dispatchEvent(new Event('change'));
}
</script>

</body>
</html>