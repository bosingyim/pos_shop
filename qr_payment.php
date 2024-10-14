<!-- qr_payment.php -->
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code ชำระเงิน</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 h-screen flex items-center justify-center">

<div class="bg-white p-8 rounded-lg shadow-lg text-center">
    <h2 class="text-2xl font-semibold mb-6">สแกน QR Code เพื่อชำระเงิน</h2>
    <img src="qr.png" alt="QR Code พร้อมเพย์" class="mx-auto mb-4" style="width: 500px; height: 500px;"> <!-- ใช้ style กำหนดขนาดตามต้องการ -->

    <p class="text-lg">กรุณาสแกน QR Code นี้ด้วยแอปธนาคารของคุณ</p>
    <a href="index.php" 
       class="mt-6 inline-block bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded">
        กลับไปหน้าตะกร้า
    </a>
</div>


</body>
</html>
