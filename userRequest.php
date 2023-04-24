<?php
require('connectMYSQL.php');
require_once('utility.php');

class userRequest{
    private static $method_type = array('get', 'put', 'delete');

    // merchant -------------------------------------------------------------------
    public static function getRequest(){
        $method = strtolower($_SERVER['REQUEST_METHOD']);
        if(in_array($method, self::$method_type)){
            $method_Func = $method.'Func';
            return self::$method_Func($_REQUEST);
        }else{
            return array("傳入格式錯誤", 400, 'Fail');
        }
    }

    // PUT Update
    private static function putFunc(){
        $body = json_decode(file_get_contents('php://input'), true);

        if(Utility::checkIsValidData(['uid','role'], $body)){
            // 必要輸入
            $uid = $body['uid'];
            $role = $body['role'];

            $sql_query = "SELECT * FROM $role WHERE uid = '$uid'";
            $data = MysqlUtility::MysqlQuery($sql_query);
            if(mysqli_num_rows($data) > 0){ // 有此帳號
                
                $row = mysqli_fetch_array($data);

                // 非必要輸入
                $name = Utility::checkIsValidData(['name'], $body) ? $body['name'] : $row['name'];
                $email = Utility::checkIsValidData(['email'], $body) ? $body['email'] : $row['email'];
                $password = Utility::checkIsValidData(['password'], $body) ? password_hash($body['password'], PASSWORD_DEFAULT) : $row['password'];
                $photo = Utility::checkIsValidData(['photo'], $body) ? $body['photo'] : $row['photo'];
                $phoneNumber = Utility::checkIsValidData(['phoneNumber'], $body) ? $body['phoneNumber'] : $row['phoneNumber'];
                $location = Utility::checkIsValidData(['location'], $body) ? $body['location'][0].','.$body['location'][1] : $row['location'];
                $intro = Utility::checkIsValidData(['intro'], $body) ? $body['intro'] : $row['intro'];

                // (更改的 email 必須不同)
                if($row['email'] != $email){    // 輸入的email與原email不同 & 該email已註冊
                    $sql_query = "SELECT * FROM $role WHERE email = '$email'";
                    $data = MysqlUtility::MysqlQuery($sql_query);
                    if(mysqli_num_rows($data) > 0){     // email 存在
                        return array("更新失敗 該email已存在", 403, 'Fail');
                    }
                }

                $sql_query = "UPDATE $role SET name = '$name', phoneNumber = '$phoneNumber', email = '$email', password = '$password', location = '$location', intro = '$intro', photo = '$photo' WHERE uid = '$uid'";
                $data = MysqlUtility::MysqlQuery($sql_query);
                
                return array("更新成功.", 200, 'Success');    
            }else{ //無此帳號
                return array("更新失敗 無此帳號", 403, 'Fail');
            }
        }else{
            return array("缺少必要資料", 401, 'Fail');
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
                MysqlUtility::MysqlQuery($sql_query);
                return array("刪除成功", 200, 'Success');
            }else{
                return array("無該帳號", 403, 'Fail');
            }
            
        }else{
            return array("缺少必要資料", 401, 'Fail');
        }
    }



    // customer  -------------------------------------------------------------------
    public static function getRequest_cus(){
        $method = strtolower($_SERVER['REQUEST_METHOD']);
        if(in_array($method, self::$method_type)){
            $method_Func = $method.'Func_cus';
            return self::$method_Func($_REQUEST);
        }else{
            return array("傳入格式錯誤", 400, 'FailSignin');
        }
    }

    // GET 獲得所有店家資訊
    private static function getFunc_cus(){
        $sql_query = "SELECT uid, name FROM merchant ORDER BY uid";
        $data = MysqlUtility::MysqlQuery($sql_query);
        $result = array();
        $numOfMerchant = mysqli_num_rows($data);
        $cnt = 0;
        while($numOfMerchant != 0){
            $row = mysqli_fetch_array($data);
            $result[$cnt] = array( "uid" => $row['uid'], "name" => $row['name']); 
            $numOfMerchant -= 1;
            $cnt += 1;
        }
        return array($result, 200, "Success");
    }

    // PUT Update
    private static function putFunc_cus(){
        $body = json_decode(file_get_contents('php://input'), true);

        if(Utility::checkIsValidData(['uid'], $body)){
            // 必要輸入
            $uid = $body['uid'];

            $sql_query = "SELECT * FROM customer WHERE uid = '$uid'";
            $data = MysqlUtility::MysqlQuery($sql_query);
            if(mysqli_num_rows($data) > 0){ // 有此帳號
                
                $row = mysqli_fetch_array($data);

                // 非必要輸入
                $name = Utility::checkIsValidData(['name'], $body) ? $body['name'] : $row['name'];
                $email = Utility::checkIsValidData(['email'], $body) ? $body['email'] : $row['email'];
                $password = Utility::checkIsValidData(['password'], $body) ? password_hash($body['password'], PASSWORD_DEFAULT) : $row['password'];
                $photo = Utility::checkIsValidData(['photo'], $body) ? $body['photo'] : $row['photo'];
                $phoneNumber = Utility::checkIsValidData(['phoneNumber'], $body) ? $body['phoneNumber'] : $row['phoneNumber'];

                // (更改的 email 必須不同)
                if($row['email'] != $email){    // 輸入的email與原email不同 & 該email已註冊
                    $sql_query = "SELECT * FROM customer WHERE email = '$email'";
                    $data = MysqlUtility::MysqlQuery($sql_query);
                    if(mysqli_num_rows($data) > 0){     // email 存在
                        return array("更新失敗 該email已存在", 403, 'Fail');
                    }
                }

                $sql_query = "UPDATE customer SET name = '$name', phoneNumber = '$phoneNumber', email = '$email', password = '$password', photo = '$photo' WHERE uid = '$uid'";
                $data = MysqlUtility::MysqlQuery($sql_query);
                
                return array("更新成功.", 200, 'Success');    
            }else{ //無此帳號
                return array("更新失敗 無此帳號", 403, 'Fail');
            }
        }else{
            return array("缺少必要資料", 401, 'Fail');
        }
        

    } 

    // Delete
    private static function deleteFunc_cus(){
        $body = json_decode(file_get_contents('php://input'), true);
        if(Utility::checkIsValidData(['uid'], $body)){
            $uid = $body['uid'];
            
            $sql_query = "SELECT * FROM customer WHERE uid = $uid";
            $data = MysqlUtility::MysqlQuery($sql_query);
            
            if(mysqli_num_rows($data) != 0){
                $sql_query = "DELETE FROM customer  WHERE uid = $uid";
                MysqlUtility::MysqlQuery($sql_query);
                return array("刪除成功", 200, 'Success');
            }else{
                return array("無該帳號", 403, 'Fail');
            }
            
        }else{
            return array("缺少必要資料", 401, 'Fail');
        }
    }
}

?>
