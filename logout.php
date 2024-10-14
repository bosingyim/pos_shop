<?php
session_start();
session_unset(); // ลบข้อมูลเซสชันทั้งหมด
session_destroy(); // ทำลายเซสชัน
header("Location: login.php"); // ส่งกลับไปที่หน้า login
exit();
?>
