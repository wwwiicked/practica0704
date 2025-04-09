<?php
require_once __DIR__ . '/helpers.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $conn = getDB();

    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    http_response_code(200);
} else {
    http_response_code(400);
}
?>
