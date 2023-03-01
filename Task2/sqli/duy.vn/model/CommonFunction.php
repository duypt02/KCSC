<?php

class CommonFunction{
    public function get_num_of_pages($sql)
    {

        
        if ($sql != '') {

            try {
                $result = (new Connect())->select($sql);
                if ((is_string($result)) || !$result) return 30;

                $each = mysqli_fetch_array($result);
                $num_of_rows = $each['count(*)'];

                $so_rows_tren_1_trang = 5;

                $num_of_pages = ceil($num_of_rows / $so_rows_tren_1_trang);
            }catch (mysqli_sql_exception $e){
                return 5;
            }
            return $num_of_pages;
        }
        return 1;
    }


}