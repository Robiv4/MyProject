<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_news'])) {
        $title = $_POST['title'];
        $content = $_POST['content'];
        $image_url = $_POST['image_url'];
        $link = $_POST['link'];

        $stmt = $conn->prepare("INSERT INTO news (title, content, image_url, link) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $title, $content, $image_url, $link);

        if ($stmt->execute()) {
            $success = "News added successfully!";
        } else {
            $error = "Error adding news: " . $stmt->error;
        }
    } elseif (isset($_POST['update_welcome'])) {
        $welcome_title = $_POST['welcome_title'];
        $welcome_content = $_POST['welcome_content'];

        $stmt = $conn->prepare("INSERT INTO homepage_content (`key`, value) VALUES ('welcome_title', ?) ON DUPLICATE KEY UPDATE value = ?");
        $stmt->bind_param("ss", $welcome_title, $welcome_title);
        if (!$stmt->execute()) {
            $error = "Error updating welcome title: " . $stmt->error;
        }

        $stmt = $conn->prepare("INSERT INTO homepage_content (`key`, value) VALUES ('welcome_content', ?) ON DUPLICATE KEY UPDATE value = ?");
        $stmt->bind_param("ss", $welcome_content, $welcome_content);
        if (!$stmt->execute()) {
            $error = "Error updating welcome content: " . $stmt->error;
        }

        if (!isset($error)) {
            $success = "Welcome message updated successfully!";
        }
    }
}

// Fetch current welcome message
$welcome_title_query = $conn->query("SELECT value FROM homepage_content WHERE `key` = 'welcome_title'");
if ($welcome_title_query === false) {
    $error = "Error fetching welcome title: " . $conn->error;
    $welcome_title = '';
} else {
    $welcome_title = $welcome_title_query->fetch_assoc()['value'] ?? '';
}

$welcome_content_query = $conn->query("SELECT value FROM homepage_content WHERE `key` = 'welcome_content'");
if ($welcome_content_query === false) {
    $error = "Error fetching welcome content: " . $conn->error;
    $welcome_content = '';
} else {
    $welcome_content = $welcome_content_query->fetch_assoc()['value'] ?? '';
}

$news_query = $conn->query("SELECT * FROM news ORDER BY created_at DESC");
if ($news_query === false) {
    $error = "Error fetching news: " . $conn->error;
    $news = [];
} else {
    $news = $news_query->fetch_all(MYSQLI_ASSOC);
}
?>

<h3>Manage Homepage</h3>

<?php if (isset($success)): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<?php if (isset($error)): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<h4>Update Welcome Message</h4>

<form method="post" action="?tab=homepage">
    <div class="mb-3">
        <label for="welcome_title" class="form-label">Welcome Title</label>
        <input type="text" class="form-control" id="welcome_title" name="welcome_title" value="<?php echo htmlspecialchars($welcome_title); ?>" required>
    </div>
    <div class="mb-3">
        <label for="welcome_content" class="form-label">Welcome Content</label>
        <textarea class="form-control" id="welcome_content" name="welcome_content" rows="3" required><?php echo htmlspecialchars($welcome_content); ?></textarea>
    </div>
    <button type="submit" name="update_welcome" class="btn btn-primary">Update Welcome Message</button>
</form>

<h4 class="mt-4">Add News</h4>

<form method="post" action="?tab=homepage">
    <div class="mb-3">
        <label for="title" class="form-label">Title</label>
        <input type="text" class="form-control" id="title" name="title" required>
    </div>
    <div class="mb-3">
        <label for="content" class="form-label">Content</label>
        <textarea class="form-control" id="content" name="content" rows="3" required></textarea>
    </div>
    <div class="mb-3">
        <label for="image_url" class="form-label">Image URL</label>
        <input type="url" class="form-control" id="image_url" name="image_url" required>
    </div>
    <div class="mb-3">
        <label for="link" class="form-label">Link</label>
        <input type="url" class="form-control" id="link" name="link" required>
    </div>
    <button type="submit" name="add_news" class="btn btn-primary">Add News</button>
</form>

<h4 class="mt-4">Existing News</h4>

<?php if (!empty($news)): ?>
    <table class="table">
        <thead>
            <tr>
                <th>Title</th>
                <th>Image</th>
                <th>Link</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($news as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['title']); ?></td>
                    <td><img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="News Image" style="max-width: 100px; max-height: 100px;"></td>
                    <td><a href="<?php echo htmlspecialchars($item['link']); ?>" target="_blank">View</a></td>
                    <td><?php echo $item['created_at']; ?></td>
                    <td>
                        <a href="edit_news.php?id=<?php echo $item['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                        <a href="delete_news.php?id=<?php echo $item['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this news item?')">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No news items found.</p>
<?php endif; ?>