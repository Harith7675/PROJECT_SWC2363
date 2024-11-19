<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: sign_in.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$full_name = trim($_POST['full-name']);
$address = trim($_POST['address']);
$city = trim($_POST['city']);
$postal_code = trim($_POST['postal-code']);
$country = trim($_POST['country']);
$total_price = 0;

// Calculate total price and prepare items data
$items = [];
foreach ($_SESSION['cart'] as $item) {
    $total_price += $item['price'] * $item['quantity'];
    $items[] = [
        'name' => $item['name'],
        'quantity' => $item['quantity'],
        'price' => $item['price']
    ];
}

// Insert order into orders table with serialized items
$stmt = $pdo->prepare("INSERT INTO orders (user_id, full_name, address, city, postal_code, country, total_price, items) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->execute([$user_id, $full_name, $address, $city, $postal_code, $country, $total_price, json_encode($items)]);

// Clear the cart after checkout
unset($_SESSION['cart']);

header('Location: receipt.php');
exit;
?>
