<?php
$host = 'localhost';
$dbname = 'shop';
$username = 'root'; // ปกติ username เป็น 'root'
$password = ''; // ปกติ password เป็นว่าง

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>
