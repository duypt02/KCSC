<?php
session_start();

class Controller
{
    public function index()
    {
        if ($_SESSION['role'] == 1) {
            require 'controller/AdminController.php';
            (new AdminController())->index();
            exit;
        }
        if (isset($_SESSION['username'])) {
            $this->search_news();
        } else {
            if (isset($_COOKIE['remember'])) {
                $token = $_COOKIE['remember'];
                require 'model/User.php';
                (new User('', '', '', $token))->checkRememberUser();
            }

            if (isset($_SESSION['username']) & $_SESSION['status'] == 'enable') { 
                header('Location: ?');
                exit;
            }
            require 'view/homepage.php';
        }
    }

    public function signup()
    {
        $option = $_GET['option'] ?? '';
        if ($option == 'data') {
            $name = $this->test_input($_POST['name']);
            $username = $this->test_input($_POST['username']);
            $password = $this->test_input($_POST['password']);
            require 'model/User.php';
            $result = (new User($name, $username, $password))->insert_user();
            if ($result) {
                if ($_SESSION['role'] == 1) {
                    header('Location: index.php?controller=admin');
                    exit;
                }
                header('location:index.php?action=signin');
                exit;
            } else {
                header('location:index.php?action=signup&error=Username đã tồn tại');
                exit;
            }

        } else {
            require 'view/signup.php';
        }
    }

    public function signin()
    {
        $option = $_GET['option'] ?? '';
            if ($option === 'data') {
                $username = $_POST['username'];
                $password = $_POST['password'];
                $remember = $_POST['remember'] ?? False;
                require 'model/User.php';
                
                $status = (new User('', $username, $password))->checkExistUser($remember);
                if ($status) {
                    if ($_SESSION['role'] == 1) {
                        header('Location: index.php?controller=admin');
                        exit;
                    }
                    header('Location: index.php');
                } else {
                    header('Location: index.php?action=signin&error=Đăng nhập thất bại');
                    exit;
                }
            } else {
                require 'view/signin.php';
            }
    }

    public function signout()
    {
        require 'model/User.php';
        (new User())->delete_token();
        unset($_SESSION['id_user']);
        unset($_SESSION['username']);
        unset($_SESSION['name']);
        unset($_SESSION['role']);
        unset( $_SESSION['status']);
        setcookie('remember', null, -1);
        setcookie('verify', null, -1);
        header('Location: index.php');
    }

    private function search_news(){
        $search = $_GET['search'] ?? '';
        isset($_GET['trang']) & $_GET['trang'] > 0 ? $page = $_GET['trang'] : $page = 1;
        require 'model/News.php';
        $result = (new News())->getNews('search', $search, $page);
        require 'view/user.php';
    }


    private function test_input($data)
    {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        if ($data == '') {
            header('Location:index.php?action=signup&error=Không được để trống trường nào');
            exit;
        }
        return $data;
    }

}