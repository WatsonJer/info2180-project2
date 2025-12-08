<?php
require_once '../database/config.php';
requireAdmin();

$isAjax = isset($_POST['ajax']) && $_POST['ajax'] === '1';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstname = filter_input(INPUT_POST, 'firstname', FILTER_SANITIZE_STRING);
    $lastname = filter_input(INPUT_POST, 'lastname', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    $role = filter_input(INPUT_POST, 'role', FILTER_SANITIZE_STRING);
    
    $response = ['success' => false, 'message' => ''];
    
    // Validate inputs
    if (!$firstname || !$lastname || !$email || !$password || !$role) {
        $response['message'] = 'All fields are required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Invalid email address';
    } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $password)) {
        $response['message'] = 'Password must be at least 8 characters and contain at least one number, one letter, and one capital letter';
    } elseif (!in_array($role, ['Admin', 'Member'])) {
        $response['message'] = 'Invalid role selected';
    } else {
        $conn = getDBConnection();
        
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM Users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $response['message'] = 'Email already exists';
        } else {
            // Hash password and insert user
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO Users (firstname, lastname, email, password, role) VALUES (?, ?, ?, ?, ?)");
            
            if ($stmt->execute([$firstname, $lastname, $email, $hashedPassword, $role])) {
                $response['success'] = true;
                $response['message'] = 'User added successfully!';
            } else {
                $response['message'] = 'Failed to add user';
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
    <script src="ajax.js"></script>
</head>
<body>
    <?php include '../dashboard/header.php'; ?>
    
    <div class="main-container">
        <?php include '../dashboard/sidebar.php'; ?>
        
        <div class="content">
            <h2>New User</h2>
            
            <div id="form-messages">
                <?php if (isset($error)): ?>
                    <div class="error-message"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                
                <?php if (isset($success)): ?>
                    <div class="success-message"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>
            </div>
            
            <form method="POST" action="addUser.php" class="form-horizontal">
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
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required>
                        <small>At least 8 characters with 1 number, 1 letter, and 1 capital letter</small>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="role">Role</label>
                    <select id="role" name="role" required>
                        <option value="">Select a role</option>
                        <option value="Member">Member</option>
                        <option value="Admin">Admin</option>
                    </select>
                </div>
                
                <button type="submit" class="btn-primary">Save</button>
            </form>
        </div>
    </div>
</body>
</html>