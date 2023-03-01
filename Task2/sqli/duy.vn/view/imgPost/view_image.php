<?php
//$file = $_GET["path"];
//
//echo "<img src='".$file."'>";
////$handle = fopen($file, 'r');
////$poem = fread($handle, 1);
////fclose($handle);
////echo $poem;
////$path = $_GET['path'];
////include($path);
//
//
//$handle = fopen($filename, "rb");
//$contents = fread($handle, filesize($filename));
//fclose($handle);
$basepath = '/var/www/duy.vn/view/imgPost/';
$filename =   realpath($_GET["path"]);
if(strpos($filename, $basepath) !== 0){
    echo 'path không hợp lệ';
}else{
    header("content-type: image/jpeg");
    include($filename);
}
?>
