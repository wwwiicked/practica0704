<?php
require_once __DIR__ . '/helpers.php';

function checkNotifications($conn) {
    // Получение порога уведомления из таблицы notifications
    $query = "SELECT p.id, p.name, p.quantity, n.threshold
              FROM products p
              JOIN notifications n ON p.id = n.product_id
              WHERE p.quantity <= n.threshold AND n.notified_at IS NULL";

    $result = $conn->query($query);
    $notifications = [];

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $notifications[] = $row;

            // Обновление времени уведомления в базе данных
            $notified_at = date('Y-m-d H:i:s');
            $update_query = "UPDATE notifications SET notified_at = '$notified_at' WHERE product_id = " . $row['id'];
            $conn->query($update_query);

            // Здесь можно добавить код для отправки уведомления
        }
    }
    
    return $notifications; // Возвращаем уведомления, если необходимо
}
?>
