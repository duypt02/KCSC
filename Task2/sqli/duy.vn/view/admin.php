<?php
//session_start();
//if ($_SESSION['role'] == 0) {
//    echo "Bạn không phải Admin";
//} else {
//    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <title>Admin Inteface</title>
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto">
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
        <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
        <link rel="stylesheet" href="view/css/admin.css">
        <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>
    </head>
    <body>
    <div class="container-xl">
        <div class="table-responsive">
            <div class="table-wrapper">
                <div class="table-wrapper">
                    <div class="table-title">
                        <div class="row">
                            <div class="col-sm-10"><h1><a href="?"
                                                         style="text-decoration: none; color: #566787;">Trang
                                        <b>Admin</b></a></h1></div>
                            <div class="signout-btn col-sm-2" ><a href="?action=signout"><i class="fa fa-sign-out" style="font-size:30px;color:red; padding-top: 16px;"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="table-action">
                        <a href="?controller=admin&action=create_user" class="btn btn-success" >
                            <i class="material-icons">&#xE147;</i> <span>Add New User</span></a>
                        <a href="?controller=admin&action=create_news" class="btn btn-success" >
                            <i class="material-icons">&#xE147;</i> <span>Add New Post</span></a>
                        <div class="search-box">
                            <i class="material-icons">&#xE8B6;</i>
                            <caption>
                                <form action="?controller=admin" method="post">
                                    <input type="search" name="search" placeholder="Nhập username" class="form-control">
                                </form>
                            </caption>
                        </div>
                    </div>
                </div>
                <table class="table table-striped table-hover table-bordered">
                    <thead>
                    <tr>
                        <th>ID_User</th>
                        <th>Username</th>
                        <th>Name</th>
                        <th>View Post</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php 
                    foreach ($result as $each):?>
                        <tr>
                            <td><?php echo $each->get_id_user() ?></td>
                            <td><?php echo $each->get_username() ?></td>
                            <td><?php echo $each->get_name() ?></td>
                            <td>
                                <a href="?controller=admin&action=view&id_user=<?php echo $each->get_id_user() ?>"
                                   class="view" title="View" data-toggle="tooltip"><i
                                            class="material-icons">&#xE417;</i></a>
                            </td>
                            <td>
                                <a href="?controller=admin&action=edit_user&username=<?php echo $each->get_username() ?>"
                                   class="edit" title="Edit" data-toggle="tooltip"><i
                                            class="material-icons">&#xE254;</i></a>
                                <a href="?controller=admin&action=remove_user&id_user=<?php echo $each->get_id_user() ?>"
                                   class="delete" title="Delete" data-toggle="tooltip"><i class="material-icons">&#xE872;</i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <div class="clearfix">
                    <div class="hint-text">Showing <b>5</b> User / Page</div>
                    <ul class="pagination">
                        <li class="page-item">
                            <?php
                            $tmp = $page;
                            if ($tmp == 1) {
                                $tmp = 2;
                            }
                            ?>
                            <a href="?controller=admin&trang=<?php echo $tmp - 1 ?>"><i
                                        class="fa fa-angle-double-left"></i></a>
                        </li>
                        <?php for ($i = 1; $i <= $_SESSION['page_numbers']; $i++) {
                            if ($i == $page) {
                                echo '<li class="page-item active">';
                            } else {
                                echo '<li class="page-item">';
                            }
                            ?>
                            <a href="?controller=admin&trang=<?php echo $i ?>" class="page-link">
                                <?php echo $i ?>
                            </a>
                            </li>
                        <?php } ?>
                        <li class="page-item">
                            <a href="?controller=admin&trang=<?php echo $page + 1 ?>" class="page-link"><i class="fa fa-angle-double-right"></i></a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    </body>
    </html>

<!--    --><?php
//}
//?>