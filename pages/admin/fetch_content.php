<?php
ob_start(); // Start output buffering
session_start();
include '../../includes/db_admin.php';

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$type = $_GET['type'];
$id = $_GET['id'];

try {
    $stmt = $conn_admin->prepare("SELECT * FROM $type WHERE id = ?");
    $stmt->execute([$id]);
    $content = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($content) {
        echo json_encode(['success' => true, 'title' => $content['title'] ?? '', 'content' => $content['content'] ?? '']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Content not found']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
ob_end_flush(); // End output buffering and send output to the browser
?><?php
ob_start(); // Start output buffering
session_start();
include '../../includes/db_admin.php';

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$type = $_GET['type'];
$id = $_GET['id'];

try {
    $stmt = $conn_admin->prepare("SELECT * FROM $type WHERE id = ?");
    $stmt->execute([$id]);
    $content = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($content) {
        echo json_encode(['success' => true, 'title' => $content['title'] ?? '', 'content' => $content['content'] ?? '']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Content not found']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
ob_end_flush(); // End output buffering and send output to the browser
?>