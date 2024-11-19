<?php
session_start();
include 'db.php';

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: sign_in.php');
    exit;
}

// Handle Add Product
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];

    $stmt = $pdo->prepare("INSERT INTO products (name, description, price, stock) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $description, $price, $stock]);

    header("Location: admin_dashboard.php");
    exit;
}

// Handle Update Product
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'update') {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];

    $stmt = $pdo->prepare("UPDATE products SET name = ?, price = ?, stock = ? WHERE id = ?");
    $stmt->execute([$name, $price, $stock, $id]);

    header("Location: admin_dashboard.php");
    exit;
}

// Handle Delete Product
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'delete') {
    $id = $_POST['id'];

    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$id]);

    header("Location: admin_dashboard.php");
    exit;
}

// Handle Product Search
$search = isset($_GET['search']) ? $_GET['search'] : '';
$stmt = $pdo->prepare("SELECT * FROM products WHERE name LIKE ?");
$stmt->execute(['%' . $search . '%']);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch users and orders data
$stmt = $pdo->prepare("SELECT * FROM users");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT * FROM orders");
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="admin_dashboard.css">
</head>
<body>

    <header>
        <h1>Admin Dashboard</h1>
        <p>Welcome, Admin</p>
        <nav>
            <a href="homepage.php">Sign Out</a>
        </nav>
    </header>

    <main>
        <!-- Add Product Form -->
        <section>
            <h2>Add Product</h2>
            <form action="admin_dashboard.php" method="POST">
                <input type="hidden" name="action" value="add">
                <input type="text" name="name" placeholder="Product Name" required>
                <input type="text" name="description" placeholder="Description" required>
                <input type="number" name="price" placeholder="Price" step="0.01" required>
                <input type="number" name="stock" placeholder="Stock" required>
                <button type="submit">Add Product</button>
            </form>
        </section>

        <!-- Product Search Form -->
        <section>
            <h2>Search Products</h2>
            <form method="GET" action="admin_dashboard.php">
                <input type="text" name="search" placeholder="Search Products" value="<?= htmlspecialchars($search) ?>">
                <button type="submit">Search</button>
            </form>
        </section>

        <!-- Products Management -->
        <section>
            <h2>Manage Products</h2>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Actions</th>
                </tr>
                <?php foreach ($products as $product): ?>
                    <tr>
                        <td><?= $product['id'] ?></td>
                        <td><?= htmlspecialchars($product['name']) ?></td>
                        <td><?= htmlspecialchars($product['description']) ?></td>
                        <td>RM <?= number_format($product['price'], 2) ?></td>
                        <td><?= $product['stock'] ?></td>
                        <td>
                            <!-- Update Product Form -->
                            <form action="admin_dashboard.php" method="POST" style="display:inline;">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="id" value="<?= $product['id'] ?>">
                                <input type="text" name="name" value="<?= htmlspecialchars($product['name']) ?>" required>
                                <input type="number" name="price" value="<?= number_format($product['price'], 2) ?>" required>
                                <input type="number" name="stock" value="<?= $product['stock'] ?>" required>
                                <button type="submit">Update</button>
                            </form>
                            
                            <!-- Delete Product Form -->
                            <form action="admin_dashboard.php" method="POST" style="display:inline;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $product['id'] ?>">
                                <button type="submit" onclick="return confirm('Are you sure you want to delete this product?');">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </section>

        <!-- Users Management -->
        <section>
            <h2>Manage Users</h2>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                </tr>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= $user['id'] ?></td>
                        <td><?= htmlspecialchars($user['name']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td><?= htmlspecialchars($user['role']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </section>

        <!-- Orders Management -->
        <section>
            <h2>Manage Orders</h2>
            <table>
                <tr>
                    <th>Order ID</th>
                    <th>User ID</th>
                    <th>Total Price</th>
                    <th>Date</th>
                </tr>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><?= $order['id'] ?></td>
                        <td><?= $order['user_id'] ?></td>
                        <td>RM <?= number_format($order['total_price'], 2) ?></td>
                        <td><?= $order['created_at'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </section>
    </main>

    <footer>
        <p>&copy; 2024 GS Sport Admin Panel</p>
    </footer>

</body>
</html>
