<?php
$registered_users = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$total_visits = $conn->query("SELECT SUM(visits) as count FROM page_visits")->fetch_assoc()['count'];
?>

<h3>Statistics</h3>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Registered Users</h5>
                <p class="card-text"><?php echo $registered_users; ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Total Visits</h5>
                <p class="card-text"><?php echo $total_visits; ?></p>
            </div>
        </div>
    </div>
</div>