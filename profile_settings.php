<?php
require_once 'config.php';
require_once 'functions.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    $errors = [];

    // Validate username
    if (empty($username)) {
        $errors[] = "Username is required.";
    }

    // Validate email
    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    // Check if username or email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
    $stmt->bind_param("ssi", $username, $email, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $errors[] = "Username or email already exists.";
    }

    // Validate new password
    if (!empty($new_password)) {
        if (strlen($new_password) < 8) {
            $errors[] = "Password must be at least 8 characters long.";
        } elseif ($new_password !== $confirm_password) {
            $errors[] = "Passwords do not match.";
        }
    }

    // Process profile picture upload
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['profile_picture']['name'];
        $filetype = pathinfo($filename, PATHINFO_EXTENSION);
        if (!in_array(strtolower($filetype), $allowed)) {
            $errors[] = "Only JPG, JPEG, PNG, and GIF files are allowed.";
        } else {
            $new_filename = uniqid() . "." . $filetype;
            $upload_path = "uploads/" . $new_filename;
            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $upload_path)) {
                $profile_picture = $upload_path;
            } else {
                $errors[] = "Failed to upload profile picture.";
            }
        }
    }

    if (empty($errors)) {
        $update_fields = ["username = ?", "email = ?"];
        $params = [$username, $email];
        $types = "ss";

        if (!empty($new_password)) {
            $update_fields[] = "password = ?";
            $params[] = password_hash($new_password, PASSWORD_DEFAULT);
            $types .= "s";
        }

        if (isset($profile_picture)) {
            $update_fields[] = "profile_picture = ?";
            $params[] = $profile_picture;
            $types .= "s";
        }

        $params[] = $user_id;
        $types .= "i";

        $sql = "UPDATE users SET " . implode(", ", $update_fields) . " WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);

        if ($stmt->execute()) {
            $success = "Profile updated successfully.";
            // Refresh user data
            $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
        } else {
            $errors[] = "Error updating profile. Please try again.";
        }
    }
}

include 'header.php';
?>

<h2>Profile Settings</h2>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?php echo htmlspecialchars($error); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<?php if (isset($success)): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data">
    <div class="mb-3">
        <label for="username" class="form-label">Username</label>
        <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
    </div>
    <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
    </div>
    <div class="mb-3">
        <label for="new_password" class="form-label">New Password (leave blank to keep current password)</label>
        <input type="password" class="form-control" id="new_password" name="new_password">
    </div>
    <div class="mb-3">
        <label for="confirm_password" class="form-label">Confirm New Password</label>
        <input type="password" class="form-control" id="confirm_password" name="confirm_password">
    </div>
    <div class="mb-3">
        <label for="profile_picture" class="form-label">Profile Picture</label>
        <input type="file" class="form-control" id="profile_picture" name="profile_picture">
    </div>
    <div class="mb-3">
        <label class="form-label">Current Profile Picture</label>
        <br>
        <img src="<?php echo getProfilePicture($user_id); ?>" alt="Profile Picture" class="img-thumbnail" style="width: 150px; height: 150px; object-fit: cover;">
    </div>
    <button type="submit" class="btn btn-primary">Update Profile</button>
</form>

<?php include 'footer.php'; ?>