<?php
include 'db.php';

// ดึงวันปัจจุบัน
$current_date = date("Y-m-d");

// ยอดขายทั้งหมด (Total Sales)
$stmt = $conn->prepare("SELECT SUM(total_amount) AS total_sales FROM sales");
$stmt->execute();
$total_sales = $stmt->fetch(PDO::FETCH_ASSOC)['total_sales'] ?? 0;

// ยอดขายรายวัน (Daily Sales)
$stmt = $conn->prepare("SELECT SUM(total_amount) AS daily_sales FROM sales WHERE DATE(sale_date) = ?");
$stmt->execute([$current_date]);
$daily_sales = $stmt->fetch(PDO::FETCH_ASSOC)['daily_sales'] ?? 0;

// ส่งข้อมูลกลับในรูปแบบ JSON
echo json_encode([
    'total_sales' => $total_sales,
    'daily_sales' => $daily_sales
]);
?>
