<?php
require_once '../database/config.php';
requireAdmin();

$conn = getDBConnection();
$stmt = $conn->prepare("SELECT id, firstname, lastname, email, role, created_at FROM Users ORDER BY created_at DESC");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User List - Dolphin CRM</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
    <?php include '../dashboard/header.php'; ?>
    <div class="main-container">
        <?php include '../dashboard/sidebar.php'; ?>

        <div class="content">
            <h2>Users</h2>
            <p><a class="btn-primary" href="addUser.php">Add New User</a></p>

            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($users): ?>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td><?= htmlspecialchars($u['id']) ?></td>
                                <td><?= htmlspecialchars($u['firstname']) ?></td>
                                <td><?= htmlspecialchars($u['lastname']) ?></td>
                                <td><?= htmlspecialchars($u['email']) ?></td>
                                <td><?= htmlspecialchars($u['role']) ?></td>
                                <td><?= htmlspecialchars($u['created_at'] ?? '') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6">No users found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
