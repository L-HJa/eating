<?php
class MysqlUtility{
    # private static function con2Mysql(){}
    public static function MysqlQuery($query){
        // $con=mysqli_connect("localhost","user","password","db");
        $con = mysqli_connect("localhost", "eating", "eating109109_", "eating");
        if(mysqli_connect_error()){
            echo "Fail to connect to MySQL:" . mysqli_connect_error();
            die();
        }   
        $data = mysqli_query($con, $query);
        mysqli_close($con);
        return $data;
    }
}
?>