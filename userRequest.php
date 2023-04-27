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





    


}

class customerRequest{
    private static $method_type = array('post', 'put', 'delete');
    
    public static function getRequest(){
        $method = strtolower($_SERVER['REQUEST_METHOD']);
        if(in_array($method, self::$method_type)){
            $method_Func = $method.'Func';
            return self::$method_Func($_REQUEST);
        }else{
            return array("傳入格式錯誤", 400, "Fail");
        }
    }
    // PUT Update
    private static function putFunc(){
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
    private static function deleteFunc(){
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

class customerGetMerchantInfo{
    // post 獲得關鍵字相關的店家資訊
    public static function keyName(){
        $body = json_decode(file_get_contents('php://input'), true);
        if(Utility::checkIsValidData(['name', 'customerUid'], $body)){
            $name = $body['name'];
            $customerUid = $body['customerUid'];
            $sql_query = "SELECT * FROM merchant  WHERE name LIKE '%$name%' ORDER BY uid";
            $data = MysqlUtility::MysqlQuery($sql_query);
            $result = array();
            $numOfMerchant = mysqli_num_rows($data);
            $cnt = 0;
            if($numOfMerchant == 0) return array("無此名稱的店家", 201, "Success");
            while($numOfMerchant != 0){
                $row = mysqli_fetch_array($data);
                $merchantUid = $row['uid'];
                $result[$cnt] = array( "uid" => $row['uid'], "name" => $row['name'], "photo" => $row['photo'], "location" => $row['location']); 
                $favorite_sqlQuery = "SELECT * FROM favorite WHERE customerUid = $customerUid AND merchantUid = $merchantUid";
                $favorite_data = MysqlUtility::MysqlQuery($favorite_sqlQuery);
                $result[$cnt]['favorite'] = (mysqli_num_rows($favorite_data) != 0);
                $numOfMerchant -= 1;
                $cnt += 1;
            }
            return array($result, 200, "Success");
        }else{
            return array("缺少必要資料", 401, "Fail");
        }
    }
    // post 獲得指定店家的資訊
    public static function getDetails(){
        $body = json_decode(file_get_contents('php://input'), true);
        if(Utility::checkIsValidData(['uid'], $body)){
            $uid = $body['uid'];
            $sql_query = "SELECT * FROM merchant  WHERE uid = '$uid'";
            $data = MysqlUtility::MysqlQuery($sql_query);
            if(mysqli_num_rows($data) == 0) return array("無此店家", 403, "Fail");
            $row = mysqli_fetch_array($data);
            $result['uid'] = $row['uid']; 
            $result['name'] = $row['name']; 
            $result['phoneNumber'] = $row['phoneNumber']; 
            $result['location'] = $row['location']; 
            $result['intro'] = $row['intro']; 
            $result['photo'] = $row['photo']; 
            return array($result, 200, "Success");
        }else{
            return array("缺少必要資料", 401, "Fail");
        }
    }
}

class customerFavorite{
    private static $method_type = array('post', 'put', 'delete');
    
    public static function getRequest(){
        $method = strtolower($_SERVER['REQUEST_METHOD']);
        if(in_array($method, self::$method_type)){
            $method_Func = $method.'Func';
            return self::$method_Func($_REQUEST);
        }else{
            return array("傳入格式錯誤", 400, "Fail");
        }
    }
    // POST 獲取
    private static function postFunc(){
        $body = json_decode(file_get_contents('php://input'), True);
        if(Utility::checkIsValidData(['customerUid'], $body)){
            $customerUid = $body['customerUid'];
            $favorite_sqlquery = "SELECT * FROM favorite WHERE customerUid = '$customerUid'";
            $favorite_data = MysqlUtility::MysqlQuery($favorite_sqlquery);
            $numOfFData = mysqli_num_rows($favorite_data);
            if($numOfFData == 0) return array("尚未新增喜愛店家", 201, "Success");
            $cnt = 0;
            $result = array();
            while($numOfFData != 0){
                $favorite_row = mysqli_fetch_array($favorite_data);
                $merchantUid = $favorite_row['merchantUid'];
                $merchantInfo_sqlQuery = "SELECT * FROM merchant WHERE uid = $merchantUid";
                $merchantInfo_data = MysqlUtility::MysqlQuery($merchantInfo_sqlQuery);
                $merchantInfo_row = mysqli_fetch_array($merchantInfo_data);
                $result[$cnt] = array("uid" => $merchantInfo_row['uid'], "name" => $merchantInfo_row['name'], "location" => $merchantInfo_row['location'], "photo" => $merchantInfo_row['photo']);
                $numOfFData -= 1;
                $cnt += 1;
            } 
            return array($result, 200, "Success");
        }else{
            return array("缺少必要資料", 401, "Fail");
        }
    }
    // PUT 新增
    private static function putFunc(){
        $body = json_decode(file_get_contents('php://input'), True);
        if(Utility::checkIsValidData(['customerUid', 'merchantUid'], $body)){
            $customerUid = $body['customerUid'];
            $merchantUid = $body['merchantUid'];
            // 無此店家
            $check_sqlQuery = "SELECT * FROM merchant WHERE uid = $merchantUid";
            $check_data = MysqlUtility::MysqlQuery($check_sqlQuery);
            if(mysqli_num_rows($check_data) == 0)   return array("無此店家", 403, "Fail");

            $check_sqlQuery = "SELECT * FROM favorite WHERE merchantUid = $merchantUid AND customerUid = $customerUid";
            $check_data = MysqlUtility::MysqlQuery($check_sqlQuery);
            if(mysqli_num_rows($check_data) == 0){
                $sql_query = "INSERT INTO favorite (customerUid, merchantUid) VALUES ('$customerUid', '$merchantUid')";
                MysqlUtility::MysqlQuery($sql_query);
            }
            return array("更新成功.", 200, 'Success');    
        }else{
            return array("缺少必要資料", 401, "Fail");
        }
    }
    // DELETE 刪除
    private static function deleteFunc(){
        $body = json_decode(file_get_contents('php://input'), True);
        if(Utility::checkIsValidData(['customerUid', 'merchantUid'], $body)){
            $customerUid = $body['customerUid'];
            $merchantUid = $body['merchantUid'];
            $sql_query = "DELETE FROM favorite WHERE customerUid = '$customerUid'AND merchantUid = '$merchantUid'";
            MysqlUtility::MysqlQuery($sql_query);
            return array("刪除成功", 200, 'Success');
        }else{
            return array("缺少必要資料", 401, "Fail");
        }
    }
}
?>
