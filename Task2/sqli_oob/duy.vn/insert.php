<?php
include 'conndb.php';
$name=$_POST['uname'];
$email = $_POST['uemail'];

$query = "INSERT INTO user_table(user_name, user_email) VALUES( '$name', '$email');";
if ($db->exec($query)) {
    header("Location:index.php");
} else {
    echo "error with the query";
}
?>