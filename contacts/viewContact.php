<?php
require_once '../database/config.php';
requireLogin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    header('Location: ../dashboard/dashboard.php');
    exit();
}

$conn = getDBConnection();
$stmt = $conn->prepare('SELECT c.*, u.firstname AS creator_firstname, u.lastname AS creator_lastname FROM Contacts c LEFT JOIN Users u ON c.created_by = u.id WHERE c.id = ?');
$stmt->execute([$id]);
$contact = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$contact) {
    header('Location: ../dashboard/dashboard.php');
    exit();
}

function e($s) { return htmlspecialchars($s); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>View Contact - Dolphin CRM</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
    <?php include '../dashboard/header.php'; ?>
    <div class="main-container">
        <?php include '../dashboard/sidebar.php'; ?>
        <div class="content">
            <div class="content-header">
                <h2>Contact Details</h2>
                <a href="../dashboard/dashboard.php" class="btn-link">Back</a>
            </div>

            <div class="form-horizontal">
                <div class="form-row">
                    <div class="form-group">
                        <label>Full Name</label>
                        <div><?= e($contact['title'] . ' ' . $contact['firstname'] . ' ' . $contact['lastname']) ?></div>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <div><?= e($contact['email']) ?></div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Telephone</label>
                        <div><?= e($contact['telephone']) ?></div>
                    </div>
                    <div class="form-group">
                        <label>Company</label>
                        <div><?= e($contact['company']) ?></div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Type</label>
                        <div><?= e($contact['type'] === 'salesLead' ? 'Sales Lead' : ($contact['type'] === 'support' ? 'Support' : $contact['type'])) ?></div>
                    </div>
                    <div class="form-group">
                        <label>Assigned To</label>
                        <div>
                            <?php if ($contact['assigned_to']): ?>
                                <?php
                                    $stmt2 = $conn->prepare('SELECT firstname, lastname FROM Users WHERE id = ?');
                                    $stmt2->execute([$contact['assigned_to']]);
                                    $ass = $stmt2->fetch(PDO::FETCH_ASSOC);
                                    echo $ass ? e($ass['firstname'] . ' ' . $ass['lastname']) : '—';
                                ?>
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Created By</label>
                    <div><?= e($contact['creator_firstname'] . ' ' . $contact['creator_lastname']) ?></div>
                </div>

                <div class="form-group">
                    <label>Created At</label>
                    <div><?= e($contact['created_at']) ?></div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>