<?php
require_once '../database/config.php';
requireAdmin();

$conn = getDBConnection();
$stmt = $conn->prepare("SELECT id, firstname, lastname, email, role, created_at FROM Users ORDER BY created_at DESC");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// If there are no users, set an error and prevent the form from being saved
$disableSave = false;
if (empty($users)) {
    $disableSave = true;
    if (!isset($error) || empty($error)) {
        $error = 'No users found. Please add at least one user before adding contacts.';
    }
} else {
    $disableSave = false;
}

$isAjax = isset($_POST['ajax']) && $_POST['ajax'] === '1';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Gather and sanitize inputs for contact
    $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING);
    $firstname = trim(filter_input(INPUT_POST, 'firstname', FILTER_SANITIZE_STRING));
    $lastname = trim(filter_input(INPUT_POST, 'lastname', FILTER_SANITIZE_STRING));
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $telephone = trim(filter_input(INPUT_POST, 'telephone', FILTER_SANITIZE_STRING));
    $company = trim(filter_input(INPUT_POST, 'company', FILTER_SANITIZE_STRING));
    $type = filter_input(INPUT_POST, 'type', FILTER_SANITIZE_STRING);
    $assigned = isset($_POST['assigned']) ? ($_POST['assigned'] === '' ? null : (int)$_POST['assigned']) : null;

    $response = ['success' => false, 'message' => ''];

    // Prevent saving if there are no users available
    if ($disableSave) {
        $response['message'] = 'Cannot save contact: no users available to assign.';
    }
    // Validate required fields
    elseif (!$firstname || !$lastname || !$email) {
        $response['message'] = 'First name, last name, and email are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Invalid email address.';
    } elseif (!in_array($type, ['salesLead', 'support'])) {
        $response['message'] = 'Invalid contact type selected.';
    } else {
        // If an assigned user was provided, ensure it exists
        $validUserIds = array_column($users, 'id');
        if ($assigned !== null && !in_array($assigned, $validUserIds)) {
            $response['message'] = 'Selected assigned user is invalid.';
        } else {
            $conn = getDBConnection();
            $created_by = $_SESSION['user_id'] ?? null;
            if (!$created_by) {
                $response['message'] = 'You must be logged in to create a contact.';
            } else {
                // Insert contact into database
                $stmt = $conn->prepare("INSERT INTO Contacts (title, firstname, lastname, email, telephone, company, type, assigned_to, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $assignedParam = $assigned !== null ? $assigned : null;
                if ($stmt->execute([$title, $firstname, $lastname, $email, $telephone, $company, $type, $assignedParam, $created_by])) {
                    $response['success'] = true;
                    $response['message'] = 'Contact added successfully!';
                } else {
                    $response['message'] = 'Failed to add contact.';
                }
            }
        }
    }

    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    } else {
        if ($response['success']) {
            $success = $response['message'];
            // Optionally clear POST values to reset the form
            $_POST = [];
        } else {
            $error = $response['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add User - Dolphin CRM</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
    <?php include '../dashboard/header.php'; ?>
    
    <div class="main-container">
        <?php include '../dashboard/sidebar.php'; ?>
        
        <div class="content">
            <h2>New Contact</h2>
            
            <div id="form-messages" role="status" aria-live="polite">
                <?php if (isset($error)): ?>
                    <div class="error-message"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                
                <?php if (isset($success)): ?>
                    <div class="success-message"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>
            </div>
            
            <form id="contact-form" method="POST" action="addContact.php" class="form-horizontal">
                <div class="form-group">
                    <label for="title">Title</label>
                    <select id="title" name="title" required>
                        <option value="Mr">Mr.</option>
                        <option value="Mrs">Mrs.</option>
                    </select>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="firstname">First Name</label>
                        <input type="text" id="firstname" name="firstname" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="lastname">Last Name</label>
                        <input type="text" id="lastname" name="lastname" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="telephone">Telephone</label>
                        <input type="tel" id="telephone" name="telephone" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="company">Company</label>
                        <input type="text" id="company" name="company" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="type">Type</label>
                        <select id="type" name="type" required>
                            <option value="salesLead">Sales Lead</option>
                            <option value="support">Support</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="assigned">Assigned To</label>
                    <select id="assigned" name="assigned" <?= $disableSave ? 'disabled' : 'required' ?> >
                        <?php if ($disableSave): ?>
                            <option value="">No users available</option>
                        <?php else: ?>
                            <?php $selectedAssigned = $_POST['assigned'] ?? ''; ?>
                            <option value="" disabled <?= $selectedAssigned === '' ? 'selected' : '' ?>>Select a user</option>
                            <?php foreach ($users as $user): ?>
                                <option value="<?= htmlspecialchars($user['id']) ?>" <?= ($selectedAssigned == $user['id']) ? 'selected' : '' ?>><?= htmlspecialchars($user['firstname'] . ' ' . $user['lastname']) ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <button id="save-btn" type="submit" class="btn-primary" <?= $disableSave ? 'disabled' : '' ?>>Save</button>
            </form>
        </div>
    </div>
    <script src="addContact.js"></script>
</body>
</html>