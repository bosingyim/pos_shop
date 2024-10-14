<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// ดึงข้อมูลสินค้าจากฐานข้อมูล
$stmt = $conn->query("SELECT SUM(price * quantity) AS total_revenue FROM products");
$revenue = $stmt->fetch()['total_revenue'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reports</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4">Reports</h1>
        <p class="text-lg">Total Revenue: ฿<?php echo $revenue; ?></p>
        <!-- สามารถเพิ่มข้อมูลรายงานอื่น ๆ ตามต้องการ -->
    </div>
</body>
</html>
