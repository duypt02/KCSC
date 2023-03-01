<?php
session_start();

class AdminController
{
    public function index()
    {
        $search =$_POST['search'] ?? '';
        isset($_GET['trang']) & $_GET['trang'] > 0  ? $page = $_GET['trang'] : $page = 1;
        if ($search != '') {
            require 'model/User.php';
            $result = (new User())->get_users('search', $search, $page);
            require 'view/admin.php';
        } else {
            require 'model/User.php';
            $result = (new User())->get_users('default', $search, $page);
            require 'view/admin.php';
        }
    }

    public function create_user()
    {
        require 'view/signup.php';
    }

    public function view()
    {
        $id_user = $this->test_input($_GET['id_user']);
        $id = $this->test_input($_GET['id'] ?? -1);
        $search = $this->test_input(isset($_GET['search']) ? $_POST['search'] : '');
        isset($_GET['trang']) & $_GET['trang'] > 0 ? $page =$_GET['trang'] : $page = 1;
        if ($id != -1) {
            require 'model/News.php';
            $result = (new News($id))->getNews('single');
            require 'view/view_content.php';
        } else {
            require 'model/News.php';
            $result = (new News(-1, $id_user))->getNews('get_news_by_id_user', $search, $page);
            require 'view/admin_view_news.php';
        }
    }

    public function edit_user(){
        $username = $_GET['username'];
        $_SESSION['username_org'] = $username;
        require 'model/User.php';
        $result = (new User('', $username))->get_users('single');
        require 'view/admin_user_edit.php';
    }

    public function update_user(){
        $token = (isset($_SESSION['csrf_token']) ? $_SESSION['csrf_token'] : "");
        if ($token && $_POST['csrf_token'] === $token) {
            $name = $_POST['name'];
            $username =$_POST['username'];
            $password = $_POST['password'];
            $role = $_POST['role'] ?? 0;
            $status = $_POST['status'] ?? 'enable';
            $username_org = $_POST['username_org'];
            if($username != $username_org){
            require_once 'model/User.php';
            $existUser = (new User($name, $username, $password, '', '', $role, $status))->checkUser();
            if ($existUser){
                header("Location: index.php?controller=user&action=edit_user&username=".$username_org."&error=exist user");
                exit();
            }
        }
            require 'model/User.php';
            (new User($name, $username, $password, '', '', $role, $status))->update_user();
            $_SESSION['role'] == 1 ? header('Location: index.php?controller=admin') : header('Location: index.php');
            unset($_SESSION['csrf_token']);
        } else {
            die('Xác thực không hợp lệ');
        }

    }

    public function check_user(){
        if(isset($_GET['username'])){
            $username = $_POST['username'];
            require_once 'model/User.php';
            $existUser = (new User('', $username))->checkUser();
            if ($existUser){
                header("Location: index.php?controller=user&action=check_user&error=exist user");
            }else{
                header("Location: index.php?controller=user&action=check_user&error=not exist user");
            }
        }else {
            require 'view/admin_user_check.php';
        }
    }

    public function remove_user(){
        $id_user =$this->test_input($_GET['id_user']);
        $this->checkExistNews($id_user);
        $this->rm($id_user);
    }

    public function edit_news(){
        $id = $_GET['id'];
        $id_user = $_GET['id_user'];
        require 'model/News.php';
        $result = (new News($id, $id_user))->getNews('single');
        require 'view/news_edit.php';
    }

    public function update_news(){
        $id = $_POST['id'];
        $id_user = $_POST['id_user'];
        $username = $_POST['username'];
        $title = $this->test_input($_POST['title']);
        $content = $this->test_input($_POST['content']);
        $image = $_FILES['image'] ?? 0;
        require 'controller/NewsController.php';
        $path_save = (new NewsController())->insert_img($image);
        require 'model/News.php';
        (new News($id, $id_user,$username, $title, $content, $path_save))->update_news();
        header('Location: index.php?controller=admin&action=view&id_user='.$id_user);
    }
    public function remove_news(){
        $id = $_GET['id'];
        $id_user = $_GET['id_user'];
        require 'model/News.php';
        (new News($id, $id_user))->remove_news();
        header('Location: index.php?controller=admin&action=view&username='.$id_user);
    }

    public function view_image(){
        require 'view/view_image.php';

    }

    private function test_input($data)
    {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        if ($data == '' && $_GET['action'] == 'update_user') {
            header('Location:index.php?controller=admin&action=edit_user&username=' . $_SESSION['username_org'] . '&error=Không được để trống trường nào');
            exit;
        }
        return $data;
    }

    private function checkExistNews($id_user){
        include 'model/News.php';
        $result = (new News('', $id_user))->getNews();

        if($result != []) {
            (new News('', $id_user))->remove_news('all');
        }
    }

    private function rm($id_user){
        include 'model/User.php';
        (new User('', '', '', '', $id_user))->remove_user();
        header("Location: index.php");
    }



}