<?php
session_start();
$action = $_GET['action'] ?? 'index';
$controller = $_GET['controller'] ?? 'begin';
switch ($controller) {
    case 'begin':
        switch ($action) {
            case 'index':
            case 'signup':
            case 'signin':
            case 'signout':
                require 'controller/Controller.php';
                (new Controller())->$action();
                break;
        }
        break;
    case 'news':
        switch ($action) {
            case 'create':
            case 'view':
            case 'edit':
            case 'update':
            case 'remove':
                require 'controller/NewsController.php';
                (new NewsController())->$action();
                break;
        }
        break;
    case 'admin':
        if (isset($_SESSION['role'])) {
            if ($_SESSION['role'] == 1) {
        switch ($action) {
            case 'index':
            case 'create_user':
            case 'view':
            case 'edit_user':
            case 'update_user':
            case 'remove_user':
            case 'edit_news':
            case 'update_news':
            case 'remove_news':
            case 'view_image':
                require 'controller/AdminController.php';
                (new AdminController())->$action();
                break;
            case 'create_news':
                require 'controller/NewsController.php';
                (new NewsController())->create();
                break;
        }
            } else {
                echo 'Bạn không phải ADMIN';
                echo '<br> <a href="?index.php">Quay lại trang chủ</a>';
            }
        } else {
            header('Location: index.php');
        }
        break;
    case 'user':
        switch ($action) {
            case 'edit_user':
            case 'update_user':
            case 'check_user':
                require 'controller/AdminController.php';
                (new AdminController())->$action();
                break;
        }
        break;

    default:
        header('Location: index.php');
        break;

}



