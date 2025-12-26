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

            <div class="table-responsive table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th class="id-col">ID</th>
                        <th class="first-col">First Name</th>
                        <th class="last-col">Last Name</th>
                        <th class="email-col">Email</th>
                        <th class="role-col">Role</th>
                        <th class="created-col">Created</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($users): ?>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td class="id-col" data-label="ID"><?= htmlspecialchars($u['id']) ?></td>
                                <td class="first-col" data-label="First Name">
                                    <span class="avatar"><?php echo strtoupper(substr($u['firstname'],0,1) . substr($u['lastname'],0,1)); ?></span>
                                    <?= htmlspecialchars($u['firstname']) ?>
                                </td>
                                <td class="last-col" data-label="Last Name"><?= htmlspecialchars($u['lastname']) ?></td>
                                <td class="email-col" data-label="Email"><?= htmlspecialchars($u['email']) ?></td>
                                <td class="role-col" data-label="Role"><span class="role-badge role-<?= strtolower(htmlspecialchars($u['role'])) ?>"><?= htmlspecialchars($u['role']) ?></span></td>
                                <td class="created-col" data-label="Created"><?php echo htmlspecialchars(isset($u['created_at']) ? date('M j, Y', strtotime($u['created_at'])) : ''); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                            <tr><td colspan="6">No users found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
                </div>
        </div>
    </div>
</body>
</html>
