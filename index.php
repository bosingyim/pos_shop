<?php
session_start();
include 'db.php'; // เชื่อมต่อกับฐานข้อมูล

// ตรวจสอบการล็อกอิน
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// ตรวจสอบและจัดการการเพิ่มสินค้าลงในตะกร้า
if (isset($_POST['add_to_cart'])) {
    $productId = $_POST['product_id'];
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1; // รับจำนวนสินค้าที่ต้องการเพิ่ม

    // ตรวจสอบว่าตะกร้าในเซสชันมีอยู่แล้วหรือไม่
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // ดึงข้อมูลสินค้าจากฐานข้อมูลเพื่อตรวจสอบสต็อก
    $stmtCheckStock = $conn->prepare("SELECT quantity FROM products WHERE id = ?");
    $stmtCheckStock->execute([$productId]);
    $product = $stmtCheckStock->fetch(PDO::FETCH_ASSOC);

    // ตรวจสอบว่ามีสินค้าในสต็อกหรือไม่
    if ($product && $product['quantity'] >= $quantity) {
        // เพิ่มสินค้าลงในตะกร้า
        if (isset($_SESSION['cart'][$productId])) {
            $_SESSION['cart'][$productId] += $quantity; // เพิ่มจำนวนถ้าสินค้าอยู่ในตะกร้าแล้ว
        } else {
            $_SESSION['cart'][$productId] = $quantity; // เพิ่มสินค้าใหม่ไปที่ตะกร้า
        }

        // ลดจำนวนสินค้าลงในฐานข้อมูล
        $stmtUpdateStock = $conn->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ?");
        $stmtUpdateStock->execute([$quantity, $productId]);
    } else {
        echo "<script>alert('สินค้าหมดในสต็อกหรือจำนวนที่ระบุเกินกว่าคงเหลือ');</script>"; // แจ้งเตือนถ้าสินค้าหมดหรือจำนวนเกิน
    }
}

// จัดการลบสินค้าออกจากตะกร้า
if (isset($_POST['remove_from_cart'])) {
    $productId = $_POST['product_id'];
    $quantityToReturn = $_SESSION['cart'][$productId];

    // เพิ่มจำนวนสินค้าคืนในฐานข้อมูล
    $stmtRestock = $conn->prepare("UPDATE products SET quantity = quantity + ? WHERE id = ?");
    $stmtRestock->execute([$quantityToReturn, $productId]);

    // ลบสินค้าออกจากตะกร้า
    unset($_SESSION['cart'][$productId]);
}

// จัดการการอัปเดตจำนวนสินค้าในตะกร้า
if (isset($_POST['update_cart'])) {
    $productId = $_POST['product_id'];
    $newQuantity = (int)$_POST['quantity'];

    // ตรวจสอบว่ามีสินค้าในตะกร้า
    if (isset($_SESSION['cart'][$productId])) {
        // รับจำนวนสินค้าเก่า
        $oldQuantity = $_SESSION['cart'][$productId];

        // หากจำนวนใหม่มากกว่าจำนวนเก่า ให้ลดจำนวนในฐานข้อมูล
        if ($newQuantity > $oldQuantity) {
            $quantityDifference = $newQuantity - $oldQuantity;

            // ตรวจสอบสต็อกสินค้าก่อนลดจำนวน
            $stmtCheckStock = $conn->prepare("SELECT quantity FROM products WHERE id = ?");
            $stmtCheckStock->execute([$productId]);
            $product = $stmtCheckStock->fetch(PDO::FETCH_ASSOC);

            if ($product && $product['quantity'] >= $quantityDifference) {
                $_SESSION['cart'][$productId] = $newQuantity; // อัปเดตจำนวนในตะกร้า
                // ลดจำนวนในฐานข้อมูล
                $stmtUpdateStock = $conn->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ?");
                $stmtUpdateStock->execute([$quantityDifference, $productId]);
            } else {
                echo "<script>alert('ไม่สามารถอัปเดตจำนวนได้ เนื่องจากสินค้าหมด');</script>"; // แจ้งเตือนถ้าสินค้าหมด
            }
        } elseif ($newQuantity < $oldQuantity) {
            // หากจำนวนใหม่มากกว่าจำนวนเก่า ให้คืนจำนวนในฐานข้อมูล
            $quantityDifference = $oldQuantity - $newQuantity;
            $_SESSION['cart'][$productId] = $newQuantity; // อัปเดตจำนวนในตะกร้า

            // เพิ่มจำนวนในฐานข้อมูล
            $stmtRestock = $conn->prepare("UPDATE products SET quantity = quantity + ? WHERE id = ?");
            $stmtRestock->execute([$quantityDifference, $productId]);
        } else {
            // หากจำนวนเท่าเดิม ไม่ต้องทำอะไร
            $_SESSION['cart'][$productId] = $newQuantity; // อัปเดตจำนวนในตะกร้า
        }
    }
}

// คำนวณราคารวมในตะกร้า
$totalPrice = 0;
if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $id => $quantity) {
        $stmtPrice = $conn->prepare("SELECT price FROM products WHERE id = ?");
        $stmtPrice->execute([$id]);
        $product = $stmtPrice->fetch(PDO::FETCH_ASSOC);
        $totalPrice += $product['price'] * $quantity;
    }
}

// ดึงข้อมูลสินค้าจากฐานข้อมูล
$stmt = $conn->prepare("SELECT * FROM products");
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ดึงข้อมูลสินค้าที่อยู่ในตะกร้า
$productsInCart = [];
if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    $cartIds = implode(',', array_keys($_SESSION['cart'])); // ใช้ array_keys เพื่อดึงเฉพาะ ID ของสินค้า
    $stmtCart = $conn->prepare("SELECT * FROM products WHERE id IN ($cartIds)");
    $stmtCart->execute();
    $productsInCart = $stmtCart->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>samgirllov3sryleShop</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        .modal {
            display: none;
            position: fixed;
            z-index: 10;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
        }
        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 600px;
            border-radius: 8px;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-4">
    <div class="flex justify-between items-center mb-4">
    <h1 class="text-4xl font-bold bg-gradient-to-r from-purple-500 via-pink-500 to-red-500 text-transparent bg-clip-text animate-pulse">
        Welcome to samgirl POS
    </h1>
    <form action="logout.php" method="POST">
    <button type="submit" 
    class="bg-red-500 text-white p-2 rounded transition duration-300 ease-in-out 
           hover:bg-red-600 hover:scale-105 hover:shadow-lg 
           active:scale-95 active:bg-red-700 active:shadow-inner">
    ออกจากระบบ
</button>

<style>
/* เอฟเฟกต์สั่นเบาๆ เมื่อชี้เมาส์ */
button:hover {
    animation: shake 0.3s ease-in-out;
}

/* การสั่นเบา */
@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(2px); }
    50% { transform: translateX(-2px); }
    75% { transform: translateX(1px); }
}
</style>

    </form>
</div>


        <p class="mb-4">ร้านเราขายเป็นราว</p>

        <a href="dashboard.php" 
   class="bg-blue-500 text-white p-2 rounded shadow-lg transform transition-all duration-500 ease-in-out 
          hover:bg-blue-600 hover:scale-110 hover:shadow-2xl animate-pulse-custom">
    ไปที่แดชบอร์ด
</a>

<style>
@keyframes pulse-custom {
    0%, 100% { transform: scale(1); box-shadow: 0 0 8px rgba(59, 130, 246, 0.6); }
    50% { transform: scale(1.05); box-shadow: 0 0 15px rgba(59, 130, 246, 0.9); }
}

.animate-pulse-custom {
    animation: pulse-custom 2s infinite;
}
</style>

        <div class="flex justify-end">
            <button id="view-cart-btn" class="bg-green-500 text-white p-2 rounded ml-auto">ดูตะกร้าสินค้า (<?php echo isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0; ?>)</button>
        </div>

        <h2 class="text-2xl font-bold mt-6 mb-4">สินค้าที่มี</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php foreach ($products as $product): ?>
                <div class="bg-white rounded-lg shadow p-4">
                    <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="h-40 w-40 object-cover mb-2 rounded">
                    <h3 class="text-lg font-semibold"><?php echo htmlspecialchars($product['name']); ?></h3>
                    <p class="text-gray-600">ราคา: ฿<?php echo htmlspecialchars($product['price']); ?></p>
                    <p class="text-gray-500">สินค้าที่มี: <?php echo htmlspecialchars($product['quantity']); ?> ตัว</p> <!-- แสดงสินค้าคงเหลือ -->
                    <form action="" method="POST">
                        <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product['id']); ?>">
                        <input type="number" name="quantity" min="1" value="1" class="border rounded p-1 mb-2 w-full">
                        <button type="submit" name="add_to_cart" class="bg-blue-500 text-white p-2 rounded">เพิ่มลงในตะกร้า</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Modal for Cart -->
        <div id="cart-modal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h2 class="text-xl font-bold">ตะกร้า</h2>
                <table class="min-w-full">
                    <thead>
                        <tr>
                            <th class="py-2">ชื่อสินค้า</th>
                            <th class="py-2">จำนวน</th>
                            <th class="py-2">ราคา</th>
                            <th class="py-2">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($productsInCart)): ?>
                            <tr>
                                <td colspan="4" class="text-center py-2">ตะกร้าคุณว่างเปล่า.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($productsInCart as $productInCart): ?>
                                <tr>
                                    <td class="py-2"><?php echo htmlspecialchars($productInCart['name']); ?></td>
                                    <td class="py-2">
                                        <form action="" method="POST" class="flex items-center">
                                            <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($productInCart['id']); ?>">
                                            <input type="number" name="quantity" min="1" value="<?php echo $_SESSION['cart'][$productInCart['id']]; ?>" class="border rounded p-1 w-16">
                                            <button type="submit" name="update_cart" class="bg-yellow-500 text-white p-1 rounded ml-2">Update</button>
                                        </form>
                                    </td>
                                    <td class="py-2">฿<?php echo htmlspecialchars($productInCart['price']); ?> x <?php echo $_SESSION['cart'][$productInCart['id']]; ?></td>
                                    <td class="py-2">
                                        <form action="" method="POST">
                                            <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($productInCart['id']); ?>">
                                            <button type="submit" name="remove_from_cart" class="bg-red-500 text-white p-1 rounded">Remove</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
                <div class="mt-4">
                    <strong>ราคาทั้งหมด: ฿<?php echo $totalPrice; ?></strong>
                </div>
                <div class="mt-4">
                    <a href="checkout.php" class="bg-green-500 text-white p-2 rounded">ชำระเงิน</a>
                </div>
            </div>
        </div>

    </div>

    <script>
        const modal = document.getElementById('cart-modal');
        const viewCartButton = document.getElementById('view-cart-btn');
        const closeModal = document.querySelector('.close');

        viewCartButton.onclick = function() {
            modal.style.display = 'block';
        }

        closeModal.onclick = function() {
            modal.style.display = 'none';
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>
