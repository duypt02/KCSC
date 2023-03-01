<?php
session_start();

class NewsController
{

    public function create()
    {
        if($_SESSION['role'] == 0){
            require 'model/User.php';
            $verify = (new User())->verifyUser();
            if(!$verify){
                echo 'Xác thực không thành công';
                return 0;
            }
        }
        $option = $_GET['option'] ?? '';
        if (($option == 'data') && ($_SERVER['REQUEST_METHOD'] == 'POST')) {
            $id_user = htmlspecialchars($_SESSION['id_user'], ENT_QUOTES);
            $title = htmlspecialchars($_POST['title'] ?? '', ENT_QUOTES, 'utf-8');
            $content = htmlspecialchars( $_POST['content'] ?? '', ENT_QUOTES, 'utf-8');
            $image = $_FILES['image'] ?? 0;
            $path_save = $this->insert_img($image);
            require 'model/News.php';
            (new News(-1, $id_user, '', $title, $content, $path_save))->create_news();
            if ($_SESSION['role'] == 1) {
                header('Location:?controller=admin&action=view&id_user=' . $id_user);
            } else {
                header('Location: index.php');
            }
        } else {
            require 'view/news_create.php';
        }
    }

    public function insert_img($image)
    {
        $img_name = '';
        if ($image) {
            $destination_path = getcwd() . DIRECTORY_SEPARATOR . 'view/imgPost/';
//            $file_extension = explode('.', $image['name'])[1];
//            $path_save = '/view/imgPost/' . $_FILES["image"]["name"];
            $target_path = $destination_path .$_FILES["image"]["name"];
//            die($target_path);
            move_uploaded_file($_FILES['image']['tmp_name'], $target_path);
            $img_name =htmlspecialchars($_FILES["image"]["name"], ENT_QUOTES);
        }
        return $img_name;
    }


    public function view()
    {
        $id = $_GET['id'];
        require 'model/News.php';

        $result = (new News($id, $_SESSION['id_user']))->getNews('single');
        require 'view/view_content.php';

    }

    public function edit()
    {
        $id = $_GET['id'];
        $role = $_SESSION['role'] ?? '1';
        if($role == '2'){
            echo 'Tài khoản của bạn đang bị giới hạn, không thể thực hiện chức năng này';
            echo '<br/>' . '<a href="?">Hãy quay lại!</a>';
            die();
        }
        require 'model/News.php';
        $result = (new News($id, $_SESSION['id_user']))->getNews('single');
        require 'view/news_edit.php';
    }

    public function update()
    {
        $id = $_POST['id'];
        $id_user = $_SESSION['id_user'];
        $username = $_SESSION['username'];
        $title = $this->test_input($_POST['title'], 'edit', $id);
        $content = $this->test_input($_POST['content'], 'edit', $id);
        $image = $_FILES['image'] ?? 0;
        $path_save = $this->insert_img($image);
        require 'model/News.php';
        (new News($id, $id_user, $username, $title, $content, $path_save))->update_news();
        header('Location: index.php');
    }

    public function remove()
    {
        $id = $_GET['id'];
        $id_user = $_SESSION['id_user'];
        require 'model/News.php';
        (new News($id, $id_user))->remove_news();
        header('Location: index.php');
    }


    private function test_input($data, $action, $id = -1)
    {

        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        if ($data == '') {
            header('Location: index.php?controller=news&action=' . $action . '&error=error&id=' . $id);
            exit();
        }
        return $data;
    }


}