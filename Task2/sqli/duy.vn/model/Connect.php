<?php

class Connect{
    private $hostname = "mysql";
    private $username = "root";
    private $password = "03102002";
    private $db_name = "webproject";
   
    public function cnt(){
        $connect = mysqli_connect($this->hostname, $this->username, $this->password, $this->db_name);
        mysqli_set_charset($connect, 'utf8');
        return $connect;
    }
    public function dbConnection(){
        $connect =  new PDO('mysql:host=mysql;dbname=webproject;charset=utf8mb4', 'root', '03102002');
        return $connect;
    }

    public function excute($sql){
        $connect = $this->cnt();
        try {
            mysqli_query($connect, $sql);
        }
        catch (Exception $e) {
            echo $e;
            mysqli_close($connect);
        }
    }

    public function select($sql){
        $connect = $this->cnt();

        try{
            $result = mysqli_query($connect, $sql);
	   
        }
        catch (Exception $e){
            $err = mysqli_error($connect);
            mysqli_close($connect);
            return $err;
        }

        mysqli_close($connect);
        return $result;
    }

    public function selectPara($sql, $data){
        $dbConnection = $this->dbConnection();
        $stmt = $dbConnection->prepare($sql);
        $stmt->execute($data);
        $result = $stmt->fetchAll(PDO::FETCH_OBJ);
        $stmt->closeCursor();
        return $result;
    }

}

