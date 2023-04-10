<?php

require('connectMYSQL.php');

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

    // POST -------------------------------------------------------------------
    private static function postFunc(){
        $body = json_decode(file_get_contents('php://input'), true);
        if (! (empty($body['email']) || empty($body['password']) || empty($body['role']))) {
            $account = $body['email'];
            $password = $body['password'];
            $role =  ($body['role'] == 'merchant' ? 'merchant' : 'customer');

            $sql_findHashPassword = "SELECT * FROM $role WHERE email = '$account'";
            $data = MysqlUtility::MysqlQuery($sql_findHashPassword);

            // do not exist
            if(mysqli_num_rows($data) == 0){
                // $Response_data = array('account or password is wrong!'); 
                return array("登入錯誤", 403, 'FailLogin');  // email
            }
            
            $hash = mysqli_fetch_array($data, MYSQLI_ASSOC);
            
            // login -->> 傳時效性驗證碼?
            if(password_verify($password, $hash['password'])){
                $Response_data = "success";
                $hash['location'] = explode(',', $hash['location']);
                $hash['role'] = "merchant";
                # echo $hash['location'];

                // return $hash;
                return array($hash, 200, 'Success');
                
            }else{
                // $Response_data = "account or password is wrong.";
                return array("登入錯誤", 403, 'FailLogin');   // password
            }

        }else{
            // return "something loss";
            return array("漏填必填", 401, 'FailLogin');
            // account(email), password為必填項目
        } 
    }
}
?>