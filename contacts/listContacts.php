<?php
require_once '../database/config.php';
requireLogin();

$filter = $_GET['filter'] ?? 'all';
$uid = $_SESSION['user_id'] ?? null;

$conn = getDBConnection();
$sql = "SELECT id, title, firstname, lastname, email, company, type, assigned_to, created_at FROM Contacts";
$params = [];
$where = [];

if ($filter === 'sales') {
    $where[] = "type = ?";
    $params[] = 'salesLead';
} elseif ($filter === 'support') {
    $where[] = "type = ?";
    $params[] = 'support';
} elseif ($filter === 'assigned') {
    // only contacts assigned to current user
    $where[] = "assigned_to = ?";
    $params[] = $uid;
}

if (!empty($where)) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}

$sql .= ' ORDER BY created_at DESC';

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode(['success' => true, 'contacts' => $contacts]);
exit();
?>