<?php
session_start();
include 'db.php';

// เช็คการเข้าสู่ระบบ
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// เช็คว่าได้รับ product_id หรือไม่
if (!isset($_GET['product_id'])) {
    header("Location: dashboard.php");
    exit();
}

// ดึงข้อมูลสินค้า
$product_id = $_GET['product_id'];
$stmt = $conn->prepare("SELECT * FROM products WHERE id = :id");
$stmt->bindParam(':id', $product_id);
$stmt->execute();
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    header("Location: dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // อัปเดตข้อมูลสินค้า
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];

    $stmt = $conn->prepare("UPDATE products SET price = :price, quantity = :quantity WHERE id = :id");
    $stmt->bindParam(':price', $price);
    $stmt->bindParam(':quantity', $quantity);
    $stmt->bindParam(':id', $product_id);
    $stmt->execute();

    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Product</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-4">
        <h1 class="text-3xl font-bold mb-4">Edit Product</h1>
        <form action="edit_product.php?product_id=<?php echo $product_id; ?>" method="POST" class="bg-white p-4 rounded shadow">
            <input type="text" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" readonly class="border p-2 mb-4 w-full">
            <input type="number" name="price" value="<?php echo htmlspecialchars($product['price']); ?>" required class="border p-2 mb-4 w-full" placeholder="Price">
            <input type="number" name="quantity" value="<?php echo htmlspecialchars($product['quantity']); ?>" required class="border p-2 mb-4 w-full" placeholder="Quantity">
            <button type="submit" class="bg-blue-500 text-white p-2 rounded">Update Product</button>
        </form>
    </div>
</body>
</html>
