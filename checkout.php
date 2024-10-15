<?php session_start(); ?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ชำระเงิน</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 h-screen flex items-center justify-center">

<div class="bg-white p-8 rounded-lg shadow-lg max-w-md w-full">
    <h2 class="text-2xl font-semibold mb-6 text-center">เลือกวิธีการชำระเงิน</h2>

    <form action="payment.php" method="POST" class="space-y-4">
        <div>
            <label class="inline-flex items-center">
                <input type="radio" name="payment_method" value="cash" required 
                       class="form-radio text-green-500">
                <span class="ml-2">เงินสด</span>
            </label>
        </div>

        <div>
            <label class="inline-flex items-center">
                <input type="radio" name="payment_method" value="qr" required 
                       class="form-radio text-blue-500">
                <span class="ml-2">QR Code พร้อมเพย์</span>
            </label>
        </div>

        <button type="submit" 
                class="bg-green-500 hover:bg-green-600 text-white py-2 px-4 rounded w-full">
            ยืนยันการชำระเงิน
        </button>
    </form>
</div>

</body>
</html>
