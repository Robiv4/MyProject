<?php
require_once 'config.php';
require_once 'functions.php';

if (!isAdmin()) {
    header("Location: index.php");
    exit();
}

$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'tips';

include 'header.php';
?>

<h2>Admin Panel</h2>

<ul class="nav nav-tabs mb-4">
    <li class="nav-item">
        <a class="nav-link <?php echo $active_tab == 'tips' ? 'active' : ''; ?>" href="?tab=tips">Create Tip</a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?php echo $active_tab == 'users' ? 'active' : ''; ?>" href="?tab=users">Manage Users</a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?php echo $active_tab == 'homepage' ? 'active' : ''; ?>" href="?tab=homepage">Manage Homepage</a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?php echo $active_tab == 'messages' ? 'active' : ''; ?>" href="?tab=messages">Contact Messages</a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?php echo $active_tab == 'stats' ? 'active' : ''; ?>" href="?tab=stats">Statistics</a>
    </li>
</ul>

<?php
switch ($active_tab) {
    case 'tips':
        include 'admin_tips.php';
        break;
    case 'users':
        include 'admin_users.php';
        break;
    case 'homepage':
        include 'admin_homepage.php';
        break;
    case 'messages':
        include 'admin_messages.php';
        break;
    case 'stats':
        include 'admin_stats.php';
        break;
}

include 'footer.php';
?>