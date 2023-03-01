<?php
session_start();
$each = $result[0];
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <title>View Post</title>
</head>
<body>
<div class="container">
    <h1>View Post</h1>
    <?php if($_SESSION['role'] == 1){?>
        <a href="?controller=admin&action=view&id_user=<?php echo $id_user?>" class="float-md-end btn btn-primary">Back</a>
    <?php }else{ ?>
        <a href="?index.php" class="float-md-end btn btn-primary">Back</a>
    <?php } ?>
    <br>
    <div>
        <strong>ID:</strong>
        <p><?php echo $each->get_id();?></p>
    </div>

    <div>
        <strong>Title:</strong>
        <p><?php echo nl2br($each->get_title());?></p>
    </div>

    <div>
        <strong>Content:</strong>
        <p><?php echo nl2br($each->get_content());?></p>
    </div>

</div>
</body>
</html>


