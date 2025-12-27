<?php
require_once '../database/config.php';
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
    exit();
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$action = isset($_POST['action']) ? $_POST['action'] : '';

if (!$id || !$action) {
    echo json_encode(['success' => false, 'error' => 'Missing parameters']);
    exit();
}

$conn = getDBConnection();

if ($action === 'assign') {
    // Assign contact to current user
    $stmt = $conn->prepare('UPDATE Contacts SET assigned_to = ?, updated_at = NOW() WHERE id = ?');
    $stmt->execute([$_SESSION['user_id'], $id]);
    
    // Get the updated info
    $stmt = $conn->prepare('SELECT u.firstname, u.lastname, c.updated_at FROM Contacts c JOIN Users u ON c.assigned_to = u.id WHERE c.id = ?');
    $stmt->execute([$id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'assigned_to' => $result['firstname'] . ' ' . $result['lastname'],
        'updated_at' => $result['updated_at']
    ]);
    exit();
    
} elseif ($action === 'switch_type') {

    // Get current type safely
    $stmt = $conn->prepare('SELECT type FROM Contacts WHERE id = ?');
    $stmt->execute([$id]);
    $contact = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$contact) {
        echo json_encode(['success' => false, 'error' => 'Contact not found']);
        exit();
    }

    // Toggle type
    $newType = ($contact['type'] === 'support') ? 'salesLead' : 'support';

    $stmt = $conn->prepare(
        'UPDATE Contacts SET type = ?, updated_at = NOW() WHERE id = ?'
    );
    $stmt->execute([$newType, $id]);

    // Get updated timestamp
    $stmt = $conn->prepare('SELECT updated_at FROM Contacts WHERE id = ?');
    $stmt->execute([$id]);
    $updated = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'type_display' => $newType === 'salesLead' ? 'Sales Lead' : 'Support',
        'button_text' => $newType === 'support'
            ? 'Switch to Sales Lead'
            : 'Switch to Support',
        'updated_at' => $updated['updated_at']
    ]);
    exit();
    
} elseif ($action === 'add_note') {
    
    $comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';
    
    // Validate input
    if (empty($comment)) {
        echo json_encode(['success' => false, 'error' => 'Comment cannot be empty']);
        exit();
    }
    
    if (strlen($comment) > 5000) {
        echo json_encode(['success' => false, 'error' => 'Comment is too long (max 5000 characters)']);
        exit();
    }
    
    // Verify contact exists
    $stmt = $conn->prepare('SELECT id FROM Contacts WHERE id = ?');
    $stmt->execute([$id]);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'error' => 'Contact not found']);
        exit();
    }
    
    // Insert the note
    $stmt = $conn->prepare('INSERT INTO Notes (contact_id, comment, created_by) VALUES (?, ?, ?)');
    $stmt->execute([$id, $comment, $_SESSION['user_id']]);
    
    // Update contact's updated_at timestamp
    $stmt = $conn->prepare('UPDATE Contacts SET updated_at = NOW() WHERE id = ?');
    $stmt->execute([$id]);
    
    // Get user info and timestamps
    $stmt = $conn->prepare('
        SELECT u.firstname, u.lastname, n.created_at 
        FROM Notes n 
        JOIN Users u ON n.created_by = u.id 
        WHERE n.id = LAST_INSERT_ID()
    ');
    $stmt->execute();
    $noteData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $stmt = $conn->prepare('SELECT updated_at FROM Contacts WHERE id = ?');
    $stmt->execute([$id]);
    $contactData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'user_name' => htmlspecialchars($noteData['firstname'] . ' ' . $noteData['lastname']),
        'comment' => htmlspecialchars($comment),
        'created_at' => date('F j, Y \a\t g:ia', strtotime($noteData['created_at'])),
        'contact_updated_at' => $contactData['updated_at']
    ]);
    exit();
    
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid action']);
}
?>