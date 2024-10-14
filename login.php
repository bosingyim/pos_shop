<?php
session_start();
include 'db.php'; // เชื่อมต่อกับฐานข้อมูล

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // ตรวจสอบผู้ใช้ในฐานข้อมูล (ตัวอย่าง)
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username AND password = :password");
    $stmt->execute(['username' => $username, 'password' => $password]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $_SESSION['username'] = $user['username']; // บันทึกข้อมูลผู้ใช้ในเซสชัน
        header("Location: index.php"); // พาไปยังหน้า index.php
        exit();
    } else {
        $error = "Invalid username or password";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-4">
        <h1 class="text-3xl font-bold mb-4">Login</h1>
        <?php if (isset($error)): ?>
            <p class="text-red-500"><?php echo $error; ?></p>
        <?php endif; ?>
        <form action="" method="POST" class="bg-white p-4 rounded shadow">
            <input type="text" name="username" placeholder="Username" required class="border p-2 mb-4 w-full">
            <input type="password" name="password" placeholder="Password" required class="border p-2 mb-4 w-full">
            <button type="submit" class="bg-blue-500 text-white p-2 rounded">Login</button>
        </form>
    </div>
</body>
</html>