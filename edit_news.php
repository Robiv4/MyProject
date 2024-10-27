<?php
require_once 'config.php';
require_once 'functions.php';

if (!isAdmin()) {
    header("Location: index.php");
    exit();
}

$id = $_GET['id'] ?? 0;
$news = $conn->query("SELECT * FROM news WHERE id = $id")->fetch_assoc();

if (!$news) {
    header("Location: admin.php?tab=homepage");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $image_url = $_POST['image_url'];
    $link = $_POST['link'];

    $stmt = $conn->prepare("UPDATE news SET title = ?, content = ?, image_url = ?, link = ? WHERE id = ?");
    $stmt->bind_param("ssssi", $title, $content, $image_url, $link, $id);

    if ($stmt->execute()) {
        header("Location: admin.php?tab=homepage&success=News updated successfully");
    } else {
        $error = "Error updating news: " . $stmt->error;
    }
}

include 'header.php';
?>

<h2>Edit News</h2>

<?php if (isset($error)): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<form method="post">
    <div class="mb-3">
        <label for="title" class="form-label">Title</label>
        <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($news['title']); ?>" required>
    </div>
    <div class="mb-3">
        <label for="content" class="form-label">Content</label>
        <textarea class="form-control" id="content" name="content" rows="3" required><?php echo htmlspecialchars($news['content']); ?></textarea>
    </div>
    <div class="mb-3">
        <label for="image_url" class="form-label">Image URL</label>
        <input type="url" class="form-control" id="image_url" name="image_url" value="<?php echo htmlspecialchars($news['image_url']); ?>" required>
    </div>
    <div class="mb-3">
        <label for="link" class="form-label">Link</label>
        <input type="url" class="form-control" id="link" name="link" value="<?php echo htmlspecialchars($news['link']); ?>" required>
    </div>
    <button type="submit" class="btn btn-primary">Update News</button>
</form>

<?php include 'footer.php'; ?>