<?php
session_start();
require_once __DIR__ . '/src/helpers.php';
require_once __DIR__ . '/src/notifications_check.php';

if (!isset($_SESSION['user']['id'])) {
    header("Location: http://localhost/pr_0704/login.html");
    exit;
}

$conn = getDB();
$userId = $_SESSION['user']['id'];

// Получаем имя пользователя
$userQuery = $conn->prepare("SELECT login FROM users WHERE id = ?");
$userQuery->bind_param("i", $userId);
$userQuery->execute();
$userResult = $userQuery->get_result();
$userName = ($userResult && $userResult->num_rows > 0)
    ? $userResult->fetch_assoc()['login']
    : "Пользователь";

// Получение товаров текущего пользователя
$productQuery = $conn->prepare("SELECT p.id, p.name, p.quantity, p.price, c.name AS category
      FROM products p
      JOIN categories c ON p.category_id = c.id
      WHERE p.user_id = ?"); // Фильтруем по user_id
$productQuery->bind_param("i", $userId);
$productQuery->execute();
$productResult = $productQuery->get_result();

$products = [];
if ($productResult && $productResult->num_rows > 0) {
    while ($row = $productResult->fetch_assoc()) {
        // Получение порога уведомления из таблицы notifications
        $notificationQuery = $conn->prepare("SELECT threshold FROM notifications WHERE product_id = ?");
        $notificationQuery->bind_param("i", $row['id']);
        $notificationQuery->execute();
        $notificationResult = $notificationQuery->get_result();
        $threshold = ($notificationResult && $notificationResult->num_rows > 0)
            ? $notificationResult->fetch_assoc()['threshold']
            : null;

        // Определяем, должно ли подсвечиваться сообщение
        $lowStock = ($threshold !== null && $row['quantity'] <= $threshold);

        // Добавляем товар в массив
        $products[] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'quantity' => $row['quantity'],
            'price' => $row['price'],
            'category' => $row['category'],
            'lowStock' => $lowStock, // Добавляем информацию о низком запасе
            'threshold' => $threshold
        ];
    }
}

$conn->close(); // Закрытие соединения
?>

<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Склад | Учёт товаров</title>
  <link rel="stylesheet" href="assets/inside.css"/>
  <script>
    function toggleForm() {
      const form = document.getElementById('addProductForm');
      form.style.display = form.style.display === 'none' ? 'block' : 'none';
    }
  </script>
</head>
<body>
<div class="container">
  <header>
    <h1>Система учёта товаров</h1>
  </header>

        <div class="content-section">
          <aside class="sidebar">
            <h3>Меню</h3>
            <nav>
            <ul>
              <li><a href="#" class="active">Товары</a></li>
            </ul>
            </nav>
            <a href="src/logout.php" class="logout-btn">&larr; Выйти</a>
          </aside>
          
          <main class="main-content">
            <h2>Список товаров</h2>
                <!-- Кнопка открытия модального окна -->
            <button onclick="openModal()" class="btn btn-add">+ Добавить товар</button>

            <!-- Модальное окно -->
            <div id="addModal" class="modal">
              <div class="modal-content">
              <span class="close" onclick="closeModal()">&times;</span>
              <h3>Добавить товар</h3>
              <form action="src/add_product.php" method="POST">
              <label>Наименование:
                  <input type="text" name="name" required />
              </label>
              <label>Категория:
              <select name="category_id" required>
                <?php
                $conn = getDB();
                $catRes = mysqli_query($conn, "SELECT id, name FROM categories");
                while ($cat = mysqli_fetch_assoc($catRes)) {
                    echo '<option value="' . $cat['id'] . '">' . htmlspecialchars($cat['name']) . '</option>';
                }
                mysqli_close($conn);
                ?>
              </select>
              </label>
                <label>Количество:
                    <input type="number" name="quantity" required />
                </label>
                <label>Цена за единицу:
                    <input type="number" step="0.01" name="price" required />
                </label>
                <label>Порог уведомления:
                    <input type="number" name="threshold" required />
                </label>
                <button type="submit">Сохранить</button>
              </form>
              </div>
            </div>

            <div id="editModal" class="modal" style="display:none;">
                <div class="modal-content">
                <span onclick="closeEditModal()" class="close">&times;</span>
                <h2>Редактировать товар</h2>
                <form id="editProductForm">
                  <input type="hidden" name="id" id="editId">
                  <label>Наименование:
                      <input type="text" name="name" id="editName" required>
                  </label>
                  <label>Количество:
                      <input type="number" name="quantity" id="editQuantity" required>
                  </label>
                  <label>Цена:
                      <input type="number" step="0.01" name="price" id="editPrice" required>
                  </label>
                  <button type="submit">Сохранить</button>
                </form>
                </div>
            </div>

            <table>
            <thead>
              <tr>
                <th>Наименование</th>
                <th>Категория</th>
                <th>Количество</th>
                <th>Цена за единицу</th>
                <th>Действия</th>
              </tr>
            </thead>
              <tbody>
                <?php foreach ($products as $product): ?>
                  <tr class="<?= $product['lowStock'] ? 'low-stock' : '' ?>">
                  <td><?= htmlspecialchars($product['name']) ?></td>
                  <td><?= htmlspecialchars($product['category']) ?></td>
                  <td><?= $product['quantity'] ?> <?= $product['lowStock'] ? '<span class="warning">мало товара</span>' : '' ?></td>
                  <td><?= number_format($product['price'], 2, ',', ' ') ?>₽</td>
                  <td>
                      <button onclick="openEditModal(<?= $product['id'] ?>, '<?= htmlspecialchars($product['name']) ?>', <?= $product['quantity'] ?>, <?= $product['price'] ?>)" class="action-link">Редактировать</button>
                      <a href="#" onclick="deleteProduct(<?= $product['id'] ?>)" class="action-link delete">Удалить</a>
                  </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
            </main>
        </div>

        <footer>
            <p>&copy; 2025 Склад CRM. Все права защищены.</p>
        </footer>
    </div>
    <script src="assets/script.js"></script>
</body>
</html>