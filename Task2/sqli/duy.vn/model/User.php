<?php
require_once 'model/Connect.php';
require_once 'model/CommonFunction.php';
session_start();
class User{
    private $id_user;
    private $username;
    private $name;
    private $password;
    private $token;
    private $role;
    private $status;
    private $verify;

    public function __construct($name = '', $username ='', $password='', $token='', $id_user=-1, $role=0, 
                                $status = 'enable', $verify='')
    {
        $this->set_id_user($this->test_input($id_user));
        $this->set_name($this->test_input($name));
        $this->set_username($username);
        $this->set_password($password);
        $this->set_token($this->test_input($token));
        $this->set_role($role);
        $this->set_status($status);
        $this->set_verify($verify);
    }
    
    public function get_id_user(){
        return $this->id_user;
    }
    
    public function set_id_user($var){
        $this->id_user = $var;
    }

    public function get_name(){
        return $this->name;
    }

    public function set_name($var){
        $this->name = $var;
    }

    public function get_username(){
        return $this->username;
    }

    public function set_username($var){
        $this->username = $var;
    }

    public function get_password(){
        return $this->password;
    }

    public function set_password($var){
        $this->password = $var;
    }
    
    public function get_token(){
        return $this->token;
    }
    
    public function set_token($var){
        $this->token = $var;
    }
    
    public function get_role(){
        return $this->role;
    }
    
    public function set_role($var){
        $this->role = $var;
    }
    
    public function get_status(){
        return $this->status;
    }
    
    public function set_status($var){
        $this->status = $var;
    }
    
    public function get_verify(){
        return $this->verify;
    }
    
    public function set_verify($var){
        $this->verify = $var;
    }


    public function get_users($option ='default', $data = '', $page = 1){
        $so_users_tren_1_trang = 5;
        $skip = $so_users_tren_1_trang * ($page - 1);
        $sql_get_num_of_pages = '';
        if ($option == 'single') {
            $sql = "select * from users where username=?";
            $payload = array($this->get_username());
        } else if ($option == 'search') {
            $sql = "select * from users where username like ?";
            $payload = array('%'.$data.'%');
        } else {
            $sql_get_num_of_pages = "select count(*) as SL from users";
            $data_2 = array();
            $sql = "select * from users  limit $so_users_tren_1_trang offset $skip";
            $payload = array();
        }
        $num_of_pages = (new CommonFunction())->get_num_of_pages($sql_get_num_of_pages, $data_2);
        $_SESSION['page_numbers'] = $num_of_pages;
        $result = (new Connect())->selectPara($sql, $payload);
        $arr = [];
        if (count($result) > 0) {
            foreach ($result as $each) {
                $object = new self($each->name, $each->username, $each->password, $each->token,
                    $each->id_user, $each->role, $each->status);
                $arr[] = $object;
            }
        }
        return $arr;
    }

    public function insert_user(){
        if($this->checkExistUsername()){
            $sql = "insert into users(username, name, password) values(?, ?, ?)";
            $data = array($this->get_username(), $this->get_name(), $this->get_password());
            (new Connect())->selectPara($sql, $data);
            return True;
        }else{
            return False;
        }
    }

    public function update_user(){
        $username =  $_SESSION['username_org'];
        $sql = "update users 
        set name = ?, username = ?, password=?, role = ?, status =  ?
        where username= '$username'
        ";
        $data = array($this->get_name(), $this->get_username(), $this->get_password(), $this->get_role(), $this->get_status());
        (new Connect())->selectPara($sql, $data);
        if($_SESSION['role'] == 0){
            $_SESSION['username'] = $this->get_username();
            $_SESSION['name'] = $this->get_name();
        }
        unset($_SESSION['username_org']);
    }

    public function remove_user(){
        $sql = "delete from users where id_user=?";
        $data = array($this->get_id_user());
        (new Connect())->selectPara($sql, $data);
    }

    private function checkExistUsername(){
        $username = $this->get_username();
        $sql = "select * from users where username='$username'";
        $result = (new Connect())->select($sql);
        $each = mysqli_fetch_array($result);
        return $each['username'] == $username ? False : True;
    }

    public function checkRememberUser(){
        $sql = "select * from users where token = '{$this->get_token()}' limit 1";
        $result = (new Connect())->select($sql);
        $number_rows = mysqli_num_rows($result);
        if($number_rows == 1){
            $each = mysqli_fetch_array($result);
            $object = new self($each['name'], $each['username'], $each['password'], $each['token'], $each['id_user'],
                $each['role'], $each['status']);
            $_SESSION['id_user'] = $object->get_id_user();
            $_SESSION['username'] = $object->get_username();
            $_SESSION['name'] = $object->get_name();
            $_SESSION['role'] = $object->get_role();
            $_SESSION['status'] = $object->get_status();
        }
    }

    public function checkUser(){
        $sql = "select * from users where username = '{$this->get_username()}'";
        $result = (new Connect())->select($sql);
        if(mysqli_num_rows($result) == 1){
            return true;
        }else{
            return false;
        }
    }

    public function checkExistUser($remember){
        $sql = "select * from users where username = ? and password = ?";
        $data = array($this->get_username(), $this->password);
        $result = (new Connect())->selectPara($sql, $data);
        $number_rows =  count($result);
        if($number_rows == 1){
           $this->storedInfo($result, $remember);
            return True;
        }else{
            return False;
        }
    }

    public function delete_token(){
        $id_usr = $_SESSION['id_user'];
        $sql = "update users set token = '', verify='' where id_user = ?";
        $data = array($id_usr);
        (new Connect())->selectPara($sql, $data);
    }

    private function test_input($data)
    {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    private function storedInfo($result, $remember){
        $each =$result[0];
        $object = new self($each->name, $each->username, $each->password, $each->token, $each->id_user,
            $each->role, $each->status);
        $_SESSION['status'] = $object->get_status();
        if($_SESSION['status'] == 'disable') return False;
        $_SESSION['id_user'] = $object->get_id_user();
        $_SESSION['role'] = $object->get_role();
        $_SESSION['username'] = $object->get_username();
        $_SESSION['name'] = $object->get_name();
        // Tạo và lưu lại cookie verify
        $this->set_verify(uniqid('verify', true));
        $sql ="update users set verify = ? where username = ?";
        $data = array($this->get_verify(),$this->get_username() );
        (new Connect())->selectPara($sql, $data);
        setcookie('Verify',$this->get_verify(), time() + 60*60*24*30);
        //Nếu chọn lưu mật khẩu để tự động đăng nhập lần sau
        if($remember){
            $this->set_token(uniqid('user', true));
            $sql ="update users set token = ? where username = ?";
            $data = array($this->get_token(),$this->get_username());
            (new Connect())->selectPara($sql, $data);
            setcookie('remember',$this->get_token(), time() + 60*60*24*30);
        }
        return 0;
    }

    public function verifyUser(){
        $username = $_SESSION['username'];
        $veri = $_COOKIE['Verify'];
        $sql = "select * from users where username = ? and verify = ?";
        $data = array($username, $veri);
        $result = (new Connect())->selectPara($sql, $data);
        $number_rows = count($result);
        if($number_rows == 1){
            return true;
        }
        return false;
    }

}

