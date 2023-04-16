<?php

require('connectMYSQL.php');
require_once('utility.php');

class loginRequest{
    private static $method_type = array('post'); 

    public static function getRequest(){
        $method = strtolower($_SERVER['REQUEST_METHOD']);
        if(in_array($method, self::$method_type)){
            $method_Func = $method.'Func';
            return self::$method_Func($_REQUEST);
        }else{ 
            // return"is not post";
            return array("傳入格式錯誤", 400, 'FailSignin');
        }
    }

    // POST 登入 -------------------------------------------------------------------
    private static function postFunc(){
        $body = json_decode(file_get_contents('php://input'), true);
        if(Utility::checkIsValidData(['email', 'password', 'role'], $body)) {
            $email = $body['email'];
            $password = $body['password'];
            $role =  $body['role'];

            $sql_findHashPassword = "SELECT * FROM $role WHERE email = '$email'";
            $data = MysqlUtility::MysqlQuery($sql_findHashPassword);

            // 存在&帳密正確
            if(mysqli_num_rows($data) > 0){
                $row = mysqli_fetch_array($data, MYSQLI_ASSOC);
                if(password_verify($password, $row['password'])){
                    $row['location'] = explode(',', $row['location']);
                    $row['role'] = "merchant";

                    // 回傳所有個人資訊
                    return array($row, 200, 'Success');
                }
            }

            return array("登入錯誤", 403, 'Fail');   

        }else{
            // email, password role 為必填項目
            return array("漏填必填", 401, 'Fail');
        } 
    }
}
?>