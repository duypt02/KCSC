<?php
require_once ('conndb.php');
$name=$_POST['name'];
$email = $_POST['email'];
$id = $_GET['u_id'];

$query = "UPDATE user_table SET user_name = '$name', user_email = '$email' WHERE user_id = '$id';";

try {
    $db->exec($query);
    header("Location:index.php");
    // Sử dụng $dbh để thực hiện các truy vấn tới SQLite
} catch (PDOException $e) {
    echo "Lỗi kết nối tới SQLite: " . $e->getMessage();
}

?>