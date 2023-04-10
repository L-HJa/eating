<?php

require('connectMYSQL.php');

class photoRequest{
    private static $method_type = array("post", "put");
    private static $allowed_type = array("jpg", "jepg", "png");

    public static function getRequest(){
        $method = strtolower($_SERVER['REQUEST_METHOD']);
        if(in_array($method, self::$method_type)){
            $method_func = $method.'Func';
            return self::$method_func($_REQUEST);
        }else{ 
            return array("傳入格式錯誤", 400, 'FailSignin');
        }
    }

    private static function postFunc(){
        $body = json_decode(file_get_contents('php://input'), true);
        if(!(empty($body['uid']) || empty($body['role']))){
            $uid = $body['uid'];
            $role = $body['role'];
            $sql_query = "SELECT * FROM $role WHERE uid = '$uid'";
            $data = MysqlUtility::MysqlQuery($sql_query);
            $row = mysqli_fetch_array($data, MYSQLI_ASSOC);
            $ret['photo'] =  $row['photo'];
            return array($ret, 200, 'GetPhoto');
        }else{
            return array("漏填必填", 401, 'FailGetPhoto');
        }
    }

    private static function putFunc(){
        $body = json_decode(file_get_contents('php://input'), true);
        if(!(empty($body['email']) || empty($body['password']) || empty($body['image']) || empty($body['role']))){
            // encode   存DB
            $email = $body['email'];
            $password = $body['password'];
            $photo = $body['photo'];
            $role = $body['role'];

            $sql_findHashPassword = "SELECT * FROM $role WHERE email = '$email'";
            $data = MysqlUtility::MysqlQuery($sql_findHashPassword);

            // do not exist
            if(mysqli_num_rows($data) == 0){
                // $Response_data = array('account or password is wrong!'); 
                return array("帳密錯誤", 403, 'FailLogin');  // email
            }
            
            $hash = mysqli_fetch_array($data, MYSQLI_ASSOC);
            if(password_verify($password, $hash['password'])){
                $sql_query = "UPDATE $role SET  photo = '$photo' WHERE email = '$email'";
                $data = MysqlUtility::MysqlQuery($sql_query);
                return array("Success", 200, 'Success');
                
            }else{
                // $Response_data = "account or password is wrong.";
                return array("帳密錯誤", 403, 'FailLogin');   // password
            }

        }else{
            return array("id, image 為必填項目", 401, 'FailSignin');
        }

    }

}

?>