<?php

$db = new PDO('sqlite:usersb.sqlite3');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$query = "CREATE TABLE IF NOT EXISTS user_table(user_id integer primary key, 
            user_name text, user_email text)";
$db->exec($query);




?>
