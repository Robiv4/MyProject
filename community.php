<?php
require_once 'config.php';
require_once 'functions.php';

$search = isset($_GET['search']) ? $_GET['search'] : '';

if ($search) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE username LIKE ? OR email LIKE ?");
    $searchParam = "%$search%";
    $stmt->bind_param("ss", $searchParam, $searchParam);
    $stmt->execute();
    $users = $stmt->get_result();
} else {
    $users = $conn->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 20");
}

include 'header.php';
?>

<h2>Community</h2>

<form method="get" action="" class="mb-4">
    <div class="input-group">
        <input type="text" class="form-control" name="search" placeholder="Search users" value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit" class="btn btn-primary">Search</button>
    </div>
</form>

<div class="row">
    <?php while ($user = $users->fetch_assoc()): ?>
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title"><?php echo htmlspecialchars($user['username']); ?></h5>
                    <p class="card-text">Joined: <?php echo date('F j, Y', strtotime($user['created_at'])); ?></p>
                    <a href="profile.php?id=<?php echo $user['id']; ?>" class="btn btn-primary">View Profile</a>
                    <?php if (isLoggedIn() && $_SESSION['user_id'] != $user['id']): ?>
                        <a href="add_friend.php?id=<?php echo $user['id']; ?>" class="btn btn-secondary">Add Friend</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endwhile; ?>
</div>

<?php include 'footer.php'; ?>