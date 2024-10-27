<?php
$messages = $conn->query("SELECT * FROM contact_messages ORDER BY created_at DESC");
?>

<h3>Contact Messages</h3>

<table class="table">
    <thead>
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Message</th>
            <th>Created At</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($message = $messages->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($message['name']); ?></td>
                <td><?php echo htmlspecialchars($message['email']); ?></td>
                <td><?php echo htmlspecialchars(substr($message['message'], 0, 50)) . '...'; ?></td>
                <td><?php echo $message['created_at']; ?></td>
                <td>
                    <a href="view_message.php?id=<?php echo $message['id']; ?>" class="btn btn-sm btn-primary">View</a>
                    <a href="delete_message.php?id=<?php echo $message['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this message?')">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>