<?php
session_start();
include 'db.php';

// เช็คการเข้าสู่ระบบ
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// ดึงวันปัจจุบัน
$current_date = date("Y-m-d");

// สถิติยอดขายทั้งหมด (Total Sales)
$stmt = $conn->prepare("SELECT SUM(total_amount) AS total_sales FROM sales");
$stmt->execute();
$sales = $stmt->fetch(PDO::FETCH_ASSOC)['total_sales'] ?? 0;

// ยอดขายต่อวัน (Daily Sales)
$stmt = $conn->prepare("SELECT SUM(total_amount) AS daily_sales FROM sales WHERE DATE(sale_date) = ?");
$stmt->execute([$current_date]);
$daily_sales = $stmt->fetch(PDO::FETCH_ASSOC)['daily_sales'] ?? 0;

// คำนวณยอดกำไรทั้งหมด (Total Profit)
$stmt = $conn->prepare("SELECT SUM((price - cost) * quantity_sold) AS total_profit FROM products");
$stmt->execute();
$profit = $stmt->fetch(PDO::FETCH_ASSOC)['total_profit'] ?? 0;

// คำนวณยอดรวมของสินค้า (Total Quantity)
$stmt = $conn->prepare("SELECT SUM(quantity) AS total_quantity FROM products");
$stmt->execute();
$total_quantity = $stmt->fetch(PDO::FETCH_ASSOC)['total_quantity'] ?? 0;

// สินค้ายอดฮิต (Best-Selling Product)
$stmt = $conn->prepare("SELECT name, MAX(quantity_sold) AS max_sold FROM products");
$stmt->execute();
$best_selling_product = $stmt->fetch(PDO::FETCH_ASSOC);

// ดึงข้อมูลสินค้าทั้งหมด
$stmt = $conn->prepare("SELECT * FROM products");
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    <h1 class="text-4xl font-bold mb-4 text-purple-600 hover:text-purple-800 transition duration-300 ease-in-out transform hover:scale-105 shadow-lg p-2 border-2 border-purple-500 rounded-md">
    Admin Dashboard
</h1>



        <!-- แสดงวันปัจจุบัน -->
        <p class="text-lg text-gray-700 mb-2">วันที่: <?php echo date("d F Y"); ?></p>

        <!-- ปุ่มกลับไปหน้าหลัก -->
        <a href="index.php" 
   class="inline-block mb-4 bg-gray-700 text-white p-2 rounded transition-transform duration-300 hover:scale-105">
   กลับไปหน้าหลัก
</a>


        <!-- การ์ดแสดงยอดขาย รายวัน กำไร และสินค้าคงเหลือ -->
       <!-- Add floating animation to the grid items -->
<div class="grid grid-cols-2 gap-4 mb-6">
    <div class="bg-green-500 text-white p-4 rounded shadow animate-float">
        <h2 class="text-xl font-bold">ยอดขายวันนี้</h2>
        <p class="text-2xl">฿<?php echo number_format($sales, 2); ?></p>
    </div>

    <div class="bg-blue-500 text-white p-4 rounded shadow animate-float-delay">
        <h2 class="text-xl font-bold">จำนวนสินค้าทั้งหมด</h2>
        <p class="text-2xl"><?php echo htmlspecialchars($total_quantity); ?> ตัว</p>
    </div>
</div>

<!-- Add Tailwind CSS styles in <head> -->
<style>
@keyframes float {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
}
@keyframes float-delay {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-8px); }
}

.animate-float {
    animation: float 4s ease-in-out infinite;
}

.animate-float-delay {
    animation: float-delay 3.5s ease-in-out infinite;
}
</style>


        

        <h2 class="text-2xl font-bold mt-6 mb-4">เพิ่มสินค้าใหม่</h2>
<button 
    onclick="toggleForm()" 
    class="bg-blue-500 text-white p-2 rounded mb-4 flex items-center gap-2 hover:bg-blue-600 transition-transform transform hover:scale-105"
>
    <svg class="w-5 h-5 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
        <path d="M12 5v7m0 0v7m0-7h7m-7 0H5" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>
    <span>เพิ่มสินค้า</span>
</button>

<!-- ฟอร์มเพิ่มสินค้า -->
<form id="addProductForm" action="add_product.php" method="POST" enctype="multipart/form-data" 
      class="bg-white p-4 rounded shadow hidden transition-all duration-500 transform scale-90">
    <input type="text" name="name" placeholder="Product Name" required class="border p-2 mb-4 w-full focus:outline-none focus:ring-2 focus:ring-blue-500">
    <textarea name="description" placeholder="Product Description" required class="border p-2 mb-4 w-full focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
    <input type="number" name="price" placeholder="Price" required class="border p-2 mb-4 w-full focus:outline-none focus:ring-2 focus:ring-blue-500">
    <input type="number" name="quantity" placeholder="Quantity" required class="border p-2 mb-4 w-full focus:outline-none focus:ring-2 focus:ring-blue-500">
    <input type="file" name="image" required class="border p-2 mb-4 w-full focus:outline-none focus:ring-2 focus:ring-blue-500">
    <button type="submit" name="submit" 
        class="bg-green-500 text-white p-2 rounded hover:bg-green-600 transition-colors">
        เพิ่มสินค้า
    </button>
</form>

        <h2 class="text-2xl font-bold mt-6 mb-4">สินค้าที่มี</h2>
        <table class="min-w-full bg-white shadow-md rounded mb-4">
            <thead>
                <tr class="bg-gray-800 text-white">
                    <th class="py-2 px-4">ชื่อ</th>
                    <th class="py-2 px-4">คำอธิบาย</th>
                    <th class="py-2 px-4">ราคา</th>
                    <th class="py-2 px-4">จำนวน</th>
                    <th class="py-2 px-4">รูปภาพ</th>
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
                    <td class="py-2 px-4">
                        <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-16 h-16 object-cover">
                    </td>
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
