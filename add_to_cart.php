<?php
session_start();
include 'db.php';

// ตรวจสอบว่ามีการส่งข้อมูลมาจริงหรือไม่
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['product_id']) && isset($_POST['quantity'])) {
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];

    // ดึงข้อมูลสินค้าจากฐานข้อมูล
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = :product_id");
    $stmt->execute([':product_id' => $product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    // ตรวจสอบว่าสินค้ามีในระบบหรือไม่
    if ($product) {
        // เช็คว่ามีตะกร้าในเซสชันหรือไม่
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        // เพิ่มสินค้าลงในตะกร้า
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id]['quantity'] += $quantity;
        } else {
            $_SESSION['cart'][$product_id] = [
                'name' => $product['name'],
                'price' => $product['price'],
                'quantity' => $quantity
            ];
        }

        // อัพเดทจำนวนในฐานข้อมูล (ลดจำนวนสินค้า)
        $new_quantity = $product['quantity'] - $quantity;
        if ($new_quantity >= 0) {
            $update_stmt = $conn->prepare("UPDATE products SET quantity = :quantity WHERE id = :product_id");
            $update_stmt->execute([':quantity' => $new_quantity, ':product_id' => $product_id]);
        } else {
            echo "Not enough stock available.";
            exit();
        }

        // Redirect กลับไปที่ dashboard
        header("Location: dashboard.php");
        exit();
    } else {
        echo "Product not found.";
    }
} else {
    echo "Invalid request.";
}
?>
