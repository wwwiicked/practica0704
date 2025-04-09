<?php
session_start();
require_once __DIR__ . '/helpers.php';

if (!isset($_SESSION['user']['id'])) {
    header("Location: /login.html");
    exit;
}

$conn = getDB();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST['name']);
    $category_id = intval($_POST['category_id']);
    $quantity = intval($_POST['quantity']);
    $price = floatval($_POST['price']);
    $threshold = intval($_POST['threshold']);
    $user_id = $_SESSION['user']['id'];

    // Валидация
    if ($name === '' || $category_id <= 0 || $quantity < 0 || $price < 0 || $threshold < 0) {
        die("Ошибка: заполните все поля корректно.");
    }

    // Добавление товара
    $stmt = $conn->prepare("INSERT INTO products (name, category_id, quantity, price, user_id) VALUES (?, ?, ?, ?, ?)");
    if (!$stmt) {
        die("Ошибка подготовки запроса: " . $conn->error);
    }

    $stmt->bind_param("siidi", $name, $category_id, $quantity, $price, $user_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $product_id = $stmt->insert_id;

        // Добавление порога в таблицу notifications
        $notifStmt = $conn->prepare("INSERT INTO notifications (product_id, threshold) VALUES (?, ?)");
        if ($notifStmt) {
            $notifStmt->bind_param("ii", $product_id, $threshold);
            $notifStmt->execute();
        }

        header("Location: Location: http://localhost/pr_0704/main.php");
        exit;
    } else {
        die("Ошибка при добавлении товара.");
    }
}

$conn->close();
?>
