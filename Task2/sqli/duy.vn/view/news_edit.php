<?php
$each = $result[0];
if($each)
{
    $controller = $_GET['controller'] ?? 'news';
    $error = $_GET['error'] ?? '';
    if ($_SESSION['role'] == 1)
        $action = 'update_news';
    else
        $action = 'update';
}else{
    echo 'Bạn không có quyền chỉnh sửa bài viết!';

    echo '</br>'.'<a href="?">Quay lại trang chủ</a>';
    die();
}
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
    <title>Update Post</title>
</head>
<body>
<div class="container">
    <h1>Update Post</h1>
    <?php if($_SESSION['role'] == 1){?>
        <a href="?controller=admin&action=view&id_user=<?php echo $id_user?>" class="float-md-end btn btn-primary">Back</a>
    <?php }else{ ?>
        <a href="?index.php" class="float-md-end btn btn-primary">Back</a>
    <?php } ?>
    <br>
    <form action="?controller=<?php echo $controller ?>&action=<?php echo $action ?>" method="post" enctype="multipart/form-data">
        <input type="hidden" name="id_user" value="<?php echo $each->get_id_user() ?>">
        <input type="hidden" name="username" value="<?php echo $each->get_username() ?>">
        <input type="hidden" name="id" value="<?php echo $each->get_id() ?>">
        <div class="form-group">
            <label for="title">Title:</label>
            <input
                    formControlName="title"
                    id="title"
                    type="text"
                    class="form-control"
                    name="title"
                    value="<?php echo nl2br($each->get_title()); ?>"
            >
        </div>

        <div class="form-group">
            <label for="body">Body</label>
            <textarea
                    formControlName="body"
                    id="body"
                    type="text"
                    class="form-control"
                    name="content"
                    style="height: 200px"
            ><?php echo $each->get_content(); ?></textarea>
        </div>
        <div class="form-group">
            <label for="title">Ảnh:</label>
            <input
                    formControlName="image"
                    id="title"
                    type="file"
                    class="form-control"
                    name="image"
            >
            <?php if ($error != ''): ?>
                <div class="alert alert-danger">
                    <div>Không được để trống trường nào!!</div>
                </div>
            <?php endif; ?>
            <br>
        </div>
        <button class="btn btn-success" type="submit">Submit</button>
    </form>
</div>

</body>
</html>