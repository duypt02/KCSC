<?php
session_start();
if (empty($_SESSION['username'])) {
    echo "Hãy đăng nhập";
} else {

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
        <title>User Interface</title>
    </head>
    <body>
    <div class="container">
        <h1><a href="?" style="text-decoration: none; color:green">Trang Người Dùng</a></h1>
        <div class="create_search h-100">
            <p>Xin chào <a href="?controller=user&action=edit_user&username=<?php echo $_SESSION['username'] ?>" style="text-decoration: none; color: blue"><?php echo $_SESSION['name'] ?></a>
            <a href="?action=signout"><i class="fa fa-sign-out" style="font-size:30px;color:red"></i></a>
        </p>
            <p><a href="?controller=user&action=check_user">Check User</a></p>
        </div>

        <div class="create_search h-100">
            <div><a href="?controller=news&action=create" class="btn btn-success">Create New Post</a></div>
            <?php if(isset($_GET['search']) && $_GET['search'] != ''): //echo 'Kết quả tìm kiếm cho: ' . $_GET['search']; ?>
                        <div id="searchQuery">
                        </div>
                        <script>
                            var keyword = decodeURIComponent(location.search.substring(8)).replaceAll('+', ' ').split('&');

                            keyword.length === 1 ? document.write('Kết quả tìm kiếm cho: ' +
                                htmlEncode(keyword[0])) : document.write('Kết quả tìm kiếm cho: ' + htmlEncode(keyword[1].substring(7)));

                            function htmlEncode(str){
                                return String(str).replace(/[^\w. ]/gi, function(c){
                                    return '&#'+c.charCodeAt(0)+';';
                                });
                            }

                        </script>
                <?php endif; ?>
            <div class="search-bar h-100">
                <caption>
                    <form action="">
                        <input type="search" name="search" placeholder="Search Post ..." class="form-control">
                    </form>
                </caption>
            </div>
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
                        <?php if ($_SESSION['id_user'] == $each->get_id_user()) { ?>
                            <a href="?controller=news&action=view&id=<?php echo $each->get_id() ?>"
                               class="btn btn-info">View</a>
                            <a href="?controller=news&action=edit&id=<?php echo $each->get_id() ?>"
                               class="btn btn-primary">Edit</a>
                            <a href="?controller=news&action=remove&id=<?php echo $each->get_id() ?>"
                               class="btn btn-danger">Delete</a>
                        <?php }else{ ?>
<!--                                <-- Xem bài viết không phải của mình -->
                            <a href="?controller=news&action=view&id=<?php echo $each->get_id() ?>"
                               class="btn btn-info">View</a>
                <?php } ?>
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
                    <a href="?trang=<?php echo $tmp - 1 ?>&search=<?php echo $search ?>">&laquo;</a>
                </li>
                <?php for ($i = 1; $i <= $_SESSION['page_numbers']; $i++) { ?>
                    <li>
                        <?php if ($i == $page){ ?>
                        <a href="?trang=<?php echo $i ?>&search=<?php echo $search ?>" class="active">
                            <?php }else{ ?>
                            <a href="?trang=<?php echo $i ?>&search=<?php echo $search ?>">
                                <?php } ?>
                                <?php echo $i ?>
                            </a>
                    </li>
                <?php } ?>
                <li>
                    <a href="?trang=<?php echo $page + 1 ?>&search=<?php echo $search ?>">&raquo;</a>
                </li>

            </ul>
        </div>
    </div>


    </body>
    </html>
    <?php
}
?>