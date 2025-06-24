<?php
ob_start(); // Start output buffering
session_start();
include '../../includes/db_admin.php';

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$action = $_GET['action'] ?? $_POST['action'];
$type = $_GET['type'] ?? $_POST['type'];
$id = $_GET['id'] ?? $_POST['id'];

try {
    if ($action === 'approve' || $action === 'reject') {
        // Handle approve/reject
        $status = $action === 'approve' ? 'approved' : 'rejected';
        $stmt = $conn_admin->prepare("UPDATE $type SET status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);
        echo json_encode(['success' => true]);
    } elseif ($action === 'edit') {
        // Handle edit
        $title = $_POST['title'];
        $content = $_POST['content'];
        $stmt = $conn_admin->prepare("UPDATE $type SET title = ?, content = ? WHERE id = ?");
        $stmt->execute([$title, $content, $id]);
        echo json_encode(['success' => true]);
    } elseif ($action === 'delete') {
        // Handle delete
        $stmt = $conn_admin->prepare("DELETE FROM $type WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
ob_end_flush(); // End output buffering and send output to the browser
?>