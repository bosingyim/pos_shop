<?php
session_start();
include 'db.php';

// Initialize cart if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Check if product_id and quantity are set
if (isset($_POST['product_id']) && isset($_POST['quantity'])) {
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];

    // Check if the product is already in the cart
    if (array_key_exists($product_id, $_SESSION['cart'])) {
        $_SESSION['cart'][$product_id] += $quantity; // Increase the quantity
    } else {
        $_SESSION['cart'][$product_id] = $quantity; // Add new product to the cart
    }

    // Optionally, you can redirect back to the dashboard or a cart page
    header("Location: dashboard.php?message=Product added to cart");
    exit();
}

// If the product_id or quantity is not set, redirect with an error message
header("Location: dashboard.php?error=Unable to add product to cart");
exit();
?>
