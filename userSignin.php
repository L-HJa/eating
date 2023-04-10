<?php

require('connectMYSQL.php');

class signinRequest{
    
    private static $method_type = array('post'); 

    public static function getRequest(){
        $method = strtolower($_SERVER['REQUEST_METHOD']);
        if(in_array($method, self::$method_type)){
            $method_func = $method.'Func';
            return self::$method_func($_REQUEST);
        }else{
            return array("傳入格式錯誤", 400, 'FailSignin');
        }
    }

    // POST -------------------------------------------------------------------
    private static function postFunc(){
        $body = json_decode(file_get_contents('php://input'), true);
        if (! (empty($body['name']) || empty($body['email']) || empty($body['password']) || empty($body['role']))) {    // empty($body['phoneNumber']) || empty($body['location']) 
            // 必填
            $name = $body['name'];
            $email = $body['email'];
            $password = password_hash($body['password'], PASSWORD_DEFAULT);
            $role =  ($body['role'] == 'merchant' ? 'merchant' : 'customer');
            // 選填
            $phoneNumber = (empty($body['phoneNumber'])) ? NULL : $body['phoneNumber'];
            $location = (empty($body['location']) ) ? NULL : $body['location'][0].','.$body['location'][1];
            $photo = isset($body['photo']) ? $body['photo'] : NULL;

            $sql_findId = "SELECT * FROM $role WHERE email = '$email'";
            $data = MysqlUtility::MysqlQuery($sql_findId);

            // exist
            if(mysqli_num_rows($data) > 0){
                return array("帳號已存在", 403, 'FailSignin');
            }

            // create
            $sql_query = "INSERT INTO ".$role." (name, phoneNumber, email, password, location, photo) VALUES ('$name', '$phoneNumber', '$email', '$password', '$location', '$photo')";
            MysqlUtility::MysqlQuery($sql_query);
            $data = MysqlUtility::MysqlQuery($sql_findId);

            // return "create";
            //return "Success";
            return array("創建成功", 200, 'Success');
        }else{
            // return "something is loss.";
            return array("漏填必填", 401, 'FailSignin');
            //(name, email, password為必填項目)
        }
    }
}
?>