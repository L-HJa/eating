<?php
require('connectMYSQL.php');
class userRequest{
    private static $method_type = array('get', 'post', 'put', 'delete');

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
        # parse_str(json_decode(file_get_contents('php://input'), true), $body);

        if(!(empty($body['uid']) || empty($body['name']) || empty($body['email']) || empty($body['password']) || empty($body['role']))){
            $uid = $body['uid'];
            $name = $body['name'];
            $email = $body['email'];
            $password = password_hash($body['password'], PASSWORD_DEFAULT);
            $role =  ($body['role'] == 'merchant' ? 'merchant' : 'customer');

            // 非必要
            $photo = isset($body['photo']) ? $body['photo'] : NULL;
            $phoneNumber = isset($body['phoneNumber']) ? $body['phoneNumber'] : NULL;
            $location = isset($body['location']) ? $body['location'][0].','.$body['location'][1] : NULL;
            // $location = $body['location'][0].','.$body['location'][1];
            $intro = isset($body['intro']) ? $body['intro'] : NULL;
            

            //  -email  (更改的 email 必須不同)
            $sql_query = "SELECT * FROM $role WHERE uid = '$uid'";
            $data = MysqlUtility::MysqlQuery($sql_query);
            $row = mysqli_fetch_array($data);
            // email 有更改
            if($row['email'] != $email){
                $sql_query = "SELECT * FROM $role WHERE email = '$email'";
                $data = MysqlUtility::MysqlQuery($sql_query);
                if(mysqli_num_rows($data) > 0){     // email 存在
                    return array("更新失敗 該 email 已存在!", 403, 'Fail');
                }
            }


            $sql_query = "UPDATE $role SET name = '$name', phoneNumber = '$phoneNumber', email = '$email', password = '$password', location = '$location', intro = '$intro', photo = '$photo' WHERE uid = '$uid'";
            $data = MysqlUtility::MysqlQuery($sql_query);
            
            // 回傳 ??? 
            // return "Update.";
            return array("Update.", 200, 'Success');    
        }else{
            return array("漏填必填", 401, 'FailLogin');
        }
        

    } 

    // Delete
    private static function deleteFunc(){
        $body = json_decode(file_get_contents('php://input'), true);
        $uid = $body['uid'];
        $role =  ($body['role'] == 'merchant' ? 'merchant' : 'customer');
        $sql_query = "DELETE FROM $role  WHERE uid = $uid";
        $data = MysqlUtility::MysqlQuery($sql_query);
        // return "Delete.";
        return array("Delete.", 200, 'Success');

    }

}
// 早安:)))))  LIYU到此一遊 我來搞破壞喔 你在實驗室了呀

?>
