<?php
require_once 'config.php';
require_once 'functions.php';

$user_id = isset($_GET['id']) ? $_GET['id'] : $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    header("Location: index.php");
    exit();
}

include 'header.php';
?>

<h2><?php echo htmlspecialchars($user['username']); ?>'s Profile</h2>

<div class="row">
    <div class="col-md-4">
        <img src="<?php echo getProfilePicture($user['id']); ?>" alt="Profile Picture" class="img-fluid rounded-circle mb-3">
        <?php if ($user_id == $_SESSION['user_id']): ?>
            <a href="edit_profile.php" class="btn btn-primary">Edit Profile</a>
        <?php endif; ?>
    </div>
    <div class="col-md-8">
        <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
        <p><strong>Joined:</strong> <?php echo date('F j, Y', strtotime($user['created_at'])); ?></p>
        <!-- Add more user information as needed -->
    </div>
</div>

<?php include 'footer.php'; ?>