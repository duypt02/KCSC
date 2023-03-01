<?php $error = $_GET['error'] ?? '' ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <title>Create New Post</title>
</head>
<body>
<div class="container">
    <h1>Create New Post</h1>

    <a href="index.php" class="float-md-end btn btn-primary">Back</a>
    <br>
    <form action="?controller=news&action=create&option=data" method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label for="title">Title:</label>
            <input
                    formControlName="title"
                    id="title"
                    type="text"
                    class="form-control"
                    name="title"
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
                    style="height:200px"
            ></textarea>
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