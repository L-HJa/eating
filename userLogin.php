<?php

require('connectMYSQL.php');
require_once('utility.php');

class loginRequest{
    private static $method_type = array('post'); 

    // merchant -------------------------------------------------------------------
    public static function getRequest(){
        $method = strtolower($_SERVER['REQUEST_METHOD']);
        if(in_array($method, self::$method_type)){
            $method_Func = $method.'Func';
            return self::$method_Func($_REQUEST);
        }else{ 
            // return"is not post";
            return array("傳入格式錯誤", 400, 'Fail');
        }
    }

    // POST 
    private static function postFunc(){
        $body = json_decode(file_get_contents('php://input'), true);
        if(Utility::checkIsValidData(['email', 'password'], $body)) {
            $email = $body['email'];
            $password = $body['password'];

            $sql_findHashPassword = "SELECT * FROM merchant WHERE email = '$email'";
            $data = MysqlUtility::MysqlQuery($sql_findHashPassword);

            // merchant 存在&帳密正確
            if(mysqli_num_rows($data) > 0){
                $row = mysqli_fetch_array($data, MYSQLI_ASSOC);
                if(password_verify($password, $row['password'])){
                    $row['location'] = explode(',', $row['location']);
                    $row['role'] = "merchant";

                    // 回傳所有個人資訊
                    return array($row, 200, 'Success');
                }
            }

            $sql_findHashPassword = "SELECT * FROM customer WHERE email = '$email'";
            $data = MysqlUtility::MysqlQuery($sql_findHashPassword);
            // customer 存在&帳密正確
            if(mysqli_num_rows($data) > 0){
                $row = mysqli_fetch_array($data, MYSQLI_ASSOC);
                if(password_verify($password, $row['password'])){
                    // 回傳所有個人資訊
                    return array($row, 200, 'Success');
                }
            }

            return array("帳密錯誤", 403, 'Fail');   

        }else{
            // email, password role 為必填項目
            return array("缺少必要資料", 401, 'Fail');
        } 
    }



    // customer -------------------------------------------------------------------
    public static function getRequest_cus(){
        $method = strtolower($_SERVER['REQUEST_METHOD']);
        if(in_array($method, self::$method_type)){
            $method_Func = $method.'Func_cus';
            return self::$method_Func($_REQUEST);
        }else{ 
            return array("傳入格式錯誤", 400, 'Fail');
        }
    }

    // POST 
    private static function postFunc_cus(){
        $body = json_decode(file_get_contents('php://input'), true);
        if(Utility::checkIsValidData(['email', 'password'], $body)) {
            $email = $body['email'];
            $password = $body['password'];

            $sql_findHashPassword = "SELECT * FROM customer WHERE email = '$email'";
            $data = MysqlUtility::MysqlQuery($sql_findHashPassword);

            // 存在&帳密正確
            if(mysqli_num_rows($data) > 0){
                $row = mysqli_fetch_array($data, MYSQLI_ASSOC);
                if(password_verify($password, $row['password'])){
                    // 回傳所有個人資訊
                    return array($row, 200, 'Success');
                }
            }

            return array("帳密錯誤", 403, 'Fail');   

        }else{
            // email, password 為必填項目
            return array("缺少必要資料", 401, 'Fail');
        } 
    }
}
?>