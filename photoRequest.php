<?php

require('connectMYSQL.php');
require_once('Utility.php');

class photoRequest{
    private static $method_type = array("put", "delete");
    private static $allowed_type = array("jpg", "jepg", "png");

    public static function getRequest(){
        $method = strtolower($_SERVER['REQUEST_METHOD']);
        if(in_array($method, self::$method_type)){
            $method_Func = $method.'Func';
            return self::$method_Func($_REQUEST);
        }else{ 
            // return"is not post";
            return array("傳入格式錯誤", 403, 'Fail');
        }
    }

    private static function putFunc(){
        $body = json_decode(file_get_contents('php://input'), true);
        if(Utility::checkIsValidData(['uid', 'image', 'role'])){
            $uid = $body['uid'];
            $photo = $body['photo'];
            $role = $body['role'];

            $sql_findHashPassword = "SELECT * FROM $role WHERE uid = '$uid'";
            $data = MysqlUtility::MysqlQuery($sql_findHashPassword);

            if(mysqli_num_rows($data) == 0){    // 有該帳號
                $sql_query = "UPDATE $role SET  photo = '$photo' WHERE uid = '$uid'";
                $data = MysqlUtility::MysqlQuery($sql_query);
                return array("更新成功", 200, 'Success');
            }else{  // 無該帳號
                return array("無此帳號", 403, 'Fail');  
            }
            
        }else{
            return array("漏填必填", 401, 'Fail');  // ['uid', 'image', 'role']
        }
    }

    private static function deleteFunc(){
        $body = json_decode(file_get_contents('php://input'), true);
        if(Utility::checkIsValidData(['uid', 'role'], $body)){
            $uid = $body['uid'];
            $role =  $body['role'];
            
            $sql_query = "SELECT * FROM $role WHERE uid = $uid";
            $data = MysqlUtility::MysqlQuery($sql_query);
            
            if(mysqli_num_rows($data) != 0){    // 有該帳號
                $sql_query = "UPDATE $role SET photo = '' WHERE uid = $uid";
                MysqlUtility::MysqlQuery($sql_query);
                return array("刪除成功", 200, 'Success');
            }else{  // 無該帳號
                return array("無該帳號", 403, 'Fail');
            }
        }else{
            return array("漏填必填", 401, "Fail"); // ['uid', 'role']
        }
    }
}

?>