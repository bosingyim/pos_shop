<?php
session_start();
include 'db.php'; // Make sure the database connection is correct

// Function to generate a receipt
function generateReceipt($payment_method) {
    global $conn; // Use the database connection

    // Generate receipt details
    $receipt_number = uniqid('RCPT-');
    $date = date('Y-m-d');
    $payment_due = date('Y-m-d', strtotime('+7 days'));

    echo "<div id='receipt' style='max-width: 600px; margin: auto; padding: 20px; border: 1px solid #ccc; border-radius: 10px; font-family: Arial, sans-serif;'>";
    
    // Receipt Header
    echo "<h1 style='text-align: center;'>ใบเสร็จ</h1>";
    echo "<p><strong>หมายเลขใบเสร็จ:</strong> #$receipt_number</p>";
    echo "<p><strong>วันที่:</strong> $date</p>";
    echo "<p><strong>ชำระเมื่อ:</strong> $payment_due</p>";

    // Item List
    echo "<table style='width: 100%; border-collapse: collapse; margin-top: 20px;'>";
    echo "<thead style='background-color: #f2f2f2;'>
            <tr>
                <th style='border: 1px solid #ccc; padding: 8px;'>สินค้า</th>
                <th style='border: 1px solid #ccc; padding: 8px;'>ราคา (฿)</th>
                <th style='border: 1px solid #ccc; padding: 8px;'>จำนวน</th>
                <th style='border: 1px solid #ccc; padding: 8px;'>รวม (฿)</th>
            </tr>
          </thead>";
    echo "<tbody>";

    $total_amount = 0;

    foreach ($_SESSION['cart'] as $product_id => $quantity) {
        // Fetch product from the database
        $query = "SELECT name, price FROM products WHERE id = :product_id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
        $stmt->execute();
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($product) {
            $item_total = $product['price'] * $quantity;
            $total_amount += $item_total;

            echo "<tr>";
            echo "<td style='border: 1px solid #ccc; padding: 8px;'>{$product['name']}</td>";
            echo "<td style='border: 1px solid #ccc; padding: 8px;'>{$product['price']} บาท</td>";
            echo "<td style='border: 1px solid #ccc; padding: 8px;'>{$quantity}</td>";
            echo "<td style='border: 1px solid #ccc; padding: 8px;'>{$item_total} บาท</td>";
            echo "</tr>";
        } else {
            echo "<tr><td colspan='4' style='border: 1px solid #ccc; padding: 8px;'>ไม่พบข้อมูลสินค้า</td></tr>";
        }
    }

    echo "</tbody></table>";
    echo "<h2 style='margin-top: 20px; text-align: right;'>ทั้งหมด (฿): {$total_amount} บาท</h2>";

    // Print and Back Buttons
    echo "<button onclick='printReceipt()' style='background-color: #4CAF50; color: white; padding: 10px; border: none; border-radius: 5px; cursor: pointer; margin-top: 20px;'>พิมพ์ใบเสร็จ</button>";
    echo "<button onclick=\"window.location.href='index.php'\" style='background-color: #008CBA; color: white; padding: 10px; border: none; border-radius: 5px; cursor: pointer; margin-top: 20px; margin-left: 10px;'>กลับหน้าหลัก</button>";
    
    echo "</div>";

    // JavaScript for printing the receipt
    echo "
    <script>
        function printReceipt() {
            var printContents = document.getElementById('receipt').innerHTML;
            var originalContents = document.body.innerHTML;
            document.body.innerHTML = printContents;
            window.print();
            document.body.innerHTML = originalContents;
            window.location.reload(); // Refresh after printing
        }
    </script>
    ";
}

// Clear the cart after payment
function clearCart() {
    $_SESSION['cart'] = [];
}

// Handle the payment request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_method = $_POST['payment_method'];

    if ($payment_method === 'qr') {
        // Redirect to QR payment page
        header('Location: qr_payment.php');
        exit();
    } elseif ($payment_method === 'cash') {
        // Process cash payment and display receipt
        generateReceipt($payment_method);
        clearCart();
    } else {
        echo "วิธีชำระเงินไม่ถูกต้อง!";
    }
} else {
    // Redirect to cart page if accessed incorrectly
    header('Location: cart.php');
    exit();
}
?>
