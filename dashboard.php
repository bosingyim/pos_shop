<?php
session_start();
include 'db.php';

// เช็คการเข้าสู่ระบบ
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// สถิติยอดขาย
$stmt = $conn->prepare("SELECT SUM(price) AS total_sales FROM products");
$stmt->execute();
$sales = $stmt->fetch(PDO::FETCH_ASSOC)['total_sales'] ?? 0;

// ดึงข้อมูลสินค้าที่มีขาย
$stmt = $conn->prepare("SELECT * FROM products");
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// คำนวณสินค้าคงเหลือ
$total_quantity = array_sum(array_column($products, 'quantity'));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script>
        function toggleForm() {
            const form = document.getElementById('addProductForm');
            form.classList.toggle('hidden');
        }
    </script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-4">
        <h1 class="text-3xl font-bold mb-4">Admin Dashboard</h1>
        
        <!-- ปุ่มกลับไปหน้าหลัก -->
        <a href="index.php" class="inline-block mb-4 bg-gray-700 text-white p-2 rounded">กลับไปหน้าหลัก</a>

        <!-- การ์ดแสดงยอดขายและสินค้าคงเหลือ -->
        <div class="grid grid-cols-2 gap-4 mb-6">
            <div class="bg-green-500 text-white p-4 rounded shadow">
                <h2 class="text-xl font-bold">Total Sales</h2>
                <p class="text-2xl">฿<?php echo number_format($sales, 2); ?></p>
            </div>
            <div class="bg-blue-500 text-white p-4 rounded shadow">
                <h2 class="text-xl font-bold">Total Quantity</h2>
                <p class="text-2xl"><?php echo htmlspecialchars($total_quantity); ?> items</p>
            </div>
        </div>

        <h2 class="text-2xl font-bold mt-6 mb-4">Add New Product</h2>
        <button onclick="toggleForm()" class="bg-blue-500 text-white p-2 rounded mb-4">Add Product</button>

        <!-- ฟอร์มเพิ่มสินค้า -->
        <form id="addProductForm" action="add_product.php" method="POST" enctype="multipart/form-data" class="bg-white p-4 rounded shadow hidden">
            <input type="text" name="name" placeholder="Product Name" required class="border p-2 mb-4 w-full">
            <textarea name="description" placeholder="Product Description" required class="border p-2 mb-4 w-full"></textarea>
            <input type="number" name="price" placeholder="Price" required class="border p-2 mb-4 w-full">
            <input type="number" name="quantity" placeholder="Quantity" required class="border p-2 mb-4 w-full">
            <input type="file" name="image" required class="border p-2 mb-4 w-full">
            <button type="submit" name="submit" class="bg-blue-500 text-white p-2 rounded">Add Product</button>
        </form>

        <h2 class="text-2xl font-bold mt-6 mb-4">Available Products</h2>
        <table class="min-w-full bg-white shadow-md rounded mb-4">
            <thead>
                <tr class="bg-gray-800 text-white">
                    <th class="py-2 px-4">Name</th>
                    <th class="py-2 px-4">Description</th>
                    <th class="py-2 px-4">Price</th>
                    <th class="py-2 px-4">Quantity</th>
                    <th class="py-2 px-4">Image</th>
                    <th class="py-2 px-4">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                <tr class="border-b">
                    <td class="py-2 px-4"><?php echo htmlspecialchars($product['name']); ?></td>
                    <td class="py-2 px-4"><?php echo htmlspecialchars($product['description']); ?></td>
                    <td class="py-2 px-4">฿<?php echo number_format($product['price'], 2); ?></td>
                    <td class="py-2 px-4"><?php echo htmlspecialchars($product['quantity']); ?></td>
                    <td class="py-2 px-4"><img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-16 h-16 object-cover"></td>
                    <td class="py-2 px-4">
                        <form action="edit_product.php" method="GET">
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                            <button type="submit" class="bg-yellow-500 text-white p-1 rounded">Edit</button>
                        </form>
                        <form action="delete_product.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this product?');">
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                            <button type="submit" class="bg-red-500 text-white p-1 rounded">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
