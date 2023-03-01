
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <title>Update User</title>
</head>
<body>
<div class="container">
    <h1>Check User</h1>
    <a href="?" class="float-md-end btn btn-primary">Back</a>
    <br>
    <form action="?controller=user&action=check_user&username" method="post">
        <div class="form-group">
            <label for="title">Username</label>
            <input
                    formControlName="title"
                    id="title"
                    type="text"
                    class="form-control"
                    name="username"
            >
        </div>
        <br>
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger">
                <div> <?php echo $_GET['error']?></div>
            </div>
        <?php endif; ?>

        <button class="btn btn-success" type="submit">Check</button>
    </form>
</div>

</body>
</html>