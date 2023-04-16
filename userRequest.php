<?php
require('connectMYSQL.php');
require_once('utility.php');

class userRequest{
    private static $method_type = array('put', 'delete');

    public static function getRequest(){
        $method = strtolower($_SERVER['REQUEST_METHOD']);
        if(in_array($method, self::$method_type)){
            $method_Func = $method.'Func';
            return self::$method_Func($_REQUEST);
        }else{
            return array("傳入格式錯誤", 400, 'FailSignin');
        }
    }

    // PUT Update
    private static function putFunc(){
        $body = json_decode(file_get_contents('php://input'), true);

        if(Utility::checkIsValidData(['uid', 'name', 'email', 'password', 'role'], $body)){
            // 必要
            $uid = $body['uid'];
            $name = $body['name'];
            $email = $body['email'];
            $password = password_hash($body['password'], PASSWORD_DEFAULT);
            $role =  ($body['role'] == 'merchant' ? 'merchant' : 'customer');

            $sql_query = "SELECT * FROM $role WHERE uid = '$uid'";
            $data = MysqlUtility::MysqlQuery($sql_query);
            $row = mysqli_fetch_array($data);

            // 非必要
            $photo = Utility::checkIsValidData(['photo'], $body) ? $body['photo'] : $row['photo'];
            $phoneNumber = Utility::checkIsValidData(['phoneNumber'], $body) ? $body['phoneNumber'] : $row['phoneNumber'];
            $location = Utility::checkIsValidData(['location'], $body) ? $body['location'][0].','.$body['location'][1] : $row['location'];
            $intro = Utility::checkIsValidData(['intro'], $body) ? $body['intro'] : $row['intro'];

            // (更改的 email 必須不同)
            // email 不同&已註冊
            if($row['email'] != $email){
                $sql_query = "SELECT * FROM $role WHERE email = '$email'";
                $data = MysqlUtility::MysqlQuery($sql_query);
                if(mysqli_num_rows($data) > 0){     // email 存在
                    return array("更新失敗 該email已存在", 403, 'Fail');
                }
            }

            $sql_query = "UPDATE $role SET name = '$name', phoneNumber = '$phoneNumber', email = '$email', password = '$password', location = '$location', intro = '$intro', photo = '$photo' WHERE uid = '$uid'";
            $data = MysqlUtility::MysqlQuery($sql_query);
            
            return array("更新成功.", 200, 'Success');    
        }else{
            return array("漏填必填", 401, 'Fail');
        }
        

    } 

    // Delete
    private static function deleteFunc(){
        $body = json_decode(file_get_contents('php://input'), true);
        if(Utility::checkIsValidData(['uid', 'role'], $body)){
            $uid = $body['uid'];
            $role =  $body['role'];
            
            $sql_query = "SELECT * FROM $role WHERE uid = $uid";
            $data = MysqlUtility::MysqlQuery($sql_query);
            
            if(mysqli_num_rows($data) != 0){
                $sql_query = "DELETE FROM $role  WHERE uid = $uid";
                $data = MysqlUtility::MysqlQuery($sql_query);
                return array("刪除成功", 200, 'Success');
            }else{
                return array("無該帳號", 403, 'Fail');
            }
            
        }else{
            return array("漏填必填", 401, 'Fail');
        }
    }
}

?>
