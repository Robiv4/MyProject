<?php
require_once 'config.php';
require_once 'functions.php';

if (!isAdmin()) {
    header("Location: index.php");
    exit();
}

$id = $_GET['id'] ?? 0;

$stmt = $conn->prepare("DELETE FROM news WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: admin.php?tab=homepage&success=News deleted successfully");
} else {
    header("Location: admin.php?tab=homepage&error=Error deleting news: " . urlencode($stmt->error));
}