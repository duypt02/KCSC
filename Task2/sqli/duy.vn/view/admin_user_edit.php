<?php
session_start();

$each = $result[0];
$error = $_GET['error'] ?? '';
$controller = $_GET['controller'];
$token = isset($_SESSION['csrf_token']) ? $_SESSION['csrf_token'] : "";
if (!$token) {
    $token = md5(uniqid());
    $_SESSION['csrf_token']= $token;
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
    <title>Update User</title>
</head>
<body>
<div class="container">
    <h1>Update User</h1>
    <a href="?" class="float-md-end btn btn-primary">Back</a>
    <br>
    <form action="?controller=<?php echo $controller;?>&action=update_user" method="post">
        <input type="hidden" name="username_org" value="<?php echo $_GET['username']; ?>">
        <div class="form-group">
            <label for="title">Name</label>
            <input
                    formControlName="title"
                    id="title"
                    type="text"
                    class="form-control"
                    name="name"
                    value="<?php echo $each->get_name();?>"
            >
        </div>

        <div class="form-group">
            <label for="title">Username</label>
            <input
                    formControlName="title"
                    id="title"
                    type="text"
                    class="form-control"
                    name="username"
                    value="<?php echo $each->get_username()?>"
            >

        </div>

        <div class="form-group">
            <label for="title">Password</label>
            <input
                    formControlName="title"
                    id="title"
                    type="password"
                    class="form-control"
                    name="password"
                    value="<?php echo $each->get_password();?>"
            >

        </div>
<!--        --><?php // ?>
        <?php if($_SESSION['role'] == 1): ?>
        <div class="form-group">
            <label for="title">Role</label>
            <select class="form-select form-select-sm" aria-label=".form-select-sm example" name="role">
                <option  value="0">User</option>
                <option  value="1" <?php if($each->get_role() == 1): echo 'selected'; endif;?>>Admin</option>
            </select>
        </div>

            <div class="form-group">
                <label for="title">Status</label>
                <select class="form-select form-select-sm" aria-label=".form-select-sm example" name="status">
                    <option  value="enable">Enable</option>
                    <option  value="disable" <?php if($each->get_status() == 'disable'): echo 'selected'; endif;?>>Disable</option>
                </select>
            </div>
        <?php endif;?>
        <br>
        <?php if ($error != ''): ?>
            <div class="alert alert-danger">
                <div> <?php echo $_GET['error']?></div>
            </div>
        <?php endif; ?>


        <button class="btn btn-success" type="submit">Submit</button>
        <input type="hidden" name="csrf_token" value="<?php echo $token; ?>" />
    </form>
</div>

</body>
</html>