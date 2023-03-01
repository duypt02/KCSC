<?php
session_start();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-gH2yIJqKdNHPEq0n4Mqa/HGKIhSkIHeL5AyhkYV8i59U5AR6csBvApHHNl/vI1Bx"
          crossorigin="anonymous">
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="view/css/userInterface.css">
    <title>View Post</title>
</head>
<body>
<div class="container">
    <h1><a href="?controller=admin&action=view&id_user=<?php echo $id_user; ?>"
           style="text-decoration: none; color:green">View Post</a></h1>
    <div class="action" style="display: flex; justify-content: space-between">
            <caption>
                <form action="?controller=admin&action=view&id_user=<?php echo $id_user; ?>&search" method="post">
                    <input type="search" name="search" placeholder="Search..." class="form-control">
                </form>
            </caption>
        <a href="?controller=admin" class="float-md-end btn btn-primary">Back</a>
    </div>
    <table class="table table-striped">
        <thead>
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Title</th>
            <th width="250px">Action</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($result as $each): ?>
            <tr>
                <td><?php echo $each->get_id() ?></td>
                <td><?php echo $each->get_username() ?></td>
                <td><?php echo nl2br($each->get_title()) ?></td>
                <td>
                    <a href="?controller=admin&action=view&id_user=<?php echo $id_user ?>&id=<?php echo $each->get_id() ?>"
                       class="btn btn-info">View</a>
                    <a href="?controller=admin&action=edit_news&id_user=<?php echo $id_user ?>&id=<?php echo $each->get_id() ?>"
                       class="btn btn-primary">Edit</a>
                    <a href="?controller=admin&action=remove_news&id_user=<?php echo $id_user ?>&id=<?php echo $each->get_id() ?>"
                       class="btn btn-danger">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <div class="pagination">
        <ul>

            <li>
                <?php
                $tmp = $page;
                if ($tmp == 1) {
                    $tmp = 2;
                }
                ?>
                <a href="?controller=admin&action=view&id_user=<?php echo $id_user ?>&trang=<?php echo $tmp - 1 ?>">&laquo;</a>
            </li>
            <?php for ($i = 1; $i <= $_SESSION['page_numbers']; $i++) { ?>
                <li>
                    <?php if ($i == $page){ ?>
                    <a href="?controller=admin&action=view&id_user=<?php echo $id_user ?>&trang=<?php echo $i ?>"
                       class="active">
                        <?php }else{ ?>
                        <a href="?controller=admin&action=view&id_user=<?php echo $id_user ?>&trang=<?php echo $i ?>">
                            <?php } ?>
                            <?php echo $i ?>
                        </a>
                </li>
            <?php } ?>
            <li>
                <a href="?controller=admin&action=view&id_user=<?php echo $id_user ?>&trang=<?php echo $page + 1 ?>">&raquo;</a>
            </li>

        </ul>
    </div>
</div>


</body>
</html>