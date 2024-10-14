<?php
session_start();
include 'db.php';

// เช็คการเข้าสู่ระบบ
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// การจัดการการเพิ่มสินค้า
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];

    // การอัปโหลดไฟล์รูปภาพ
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($_FILES["image"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // ตรวจสอบไฟล์รูปภาพ
    if (isset($_POST["submit"])) {
        $check = getimagesize($_FILES["image"]["tmp_name"]);
        if ($check !== false) {
            $uploadOk = 1;
        } else {
            echo "File is not an image.";
            $uploadOk = 0;
        }
    }

    // ตรวจสอบว่ามีไฟล์ที่อัปโหลดหรือไม่
    if ($_FILES["image"]["size"] > 500000) {
        echo "Sorry, your file is too large.";
        $uploadOk = 0;
    }

    // ตรวจสอบนามสกุลไฟล์
    if (!in_array($imageFileType, ["jpg", "png", "jpeg", "gif"])) {
        echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        $uploadOk = 0;
    }

    // ถ้าทุกอย่างถูกต้อง ให้ทำการอัปโหลดไฟล์
    if ($uploadOk == 1) {
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            // เพิ่มข้อมูลสินค้าไปยังฐานข้อมูล
            $stmt = $conn->prepare("INSERT INTO products (name, description, price, quantity, image) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $description, $price, $quantity, $target_file]);
            echo "Product added successfully!";
            header("Location: dashboard.php"); // กลับไปที่แดชบอร์ดหลังจากเพิ่มสินค้า
            exit();
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    }
}
?>
