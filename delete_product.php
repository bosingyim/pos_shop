<?php
session_start();
include 'db.php';

// เช็คการเข้าสู่ระบบ
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// ตรวจสอบว่าได้ส่ง product_id มาหรือไม่
if (isset($_POST['product_id'])) {
    $product_id = $_POST['product_id'];

    // เตรียมคำสั่ง SQL เพื่อลบสินค้า
    $stmt = $conn->prepare("DELETE FROM products WHERE id = :id");
    $stmt->bindParam(':id', $product_id);
    
    if ($stmt->execute()) {
        // ลบสำเร็จ
        header("Location: dashboard.php?message=Product deleted successfully");
        exit();
    } else {
        // ลบไม่สำเร็จ
        header("Location: dashboard.php?message=Error deleting product");
        exit();
    }
} else {
    // ถ้าไม่มี product_id
    header("Location: dashboard.php");
    exit();
}
?>
