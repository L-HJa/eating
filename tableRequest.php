<?php

require('connectMYSQL.php');

class tableRequest{
    private static $method_type = array('post', 'delete'); 

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
        if (! (empty($body['merchantUid']) || empty($body['name']))) {
            $merchantUid = $body['merchantUid'];
            $name = $body['name'];
            
            // table is exist
            $sql_query = "SELECT * FROM tablelist WHERE merchantUid = '$merchantUid' AND name = '$name'";
            $data = MysqlUtility::MysqlQuery($sql_query);
            if(mysqli_num_rows($data) > 0){
                return array("table is exist", 403, 'fail');
            }

            $sql_query = "INSERT INTO tablelist (name, merchantUid) VALUES ('$name', '$merchantUid')";
            MysqlUtility::MysqlQuery($sql_query);

            $sql_query = "SELECT * FROM tablelist WHERE merchantUid = '$merchantUid' AND name = '$name'";
            $data = MysqlUtility::MysqlQuery($sql_query);
            $row = mysqli_fetch_array($data, MYSQLI_ASSOC);
            return array($row['uid'], 200, 'Success');
            // return 回傳格式 
           
        }
    }

    // Delete
    private static function deleteFunc(){
        $body = json_decode(file_get_contents('php://input'), true);
        $uid = $body['uid'];

        // table do not exist
        $sql_query = "SELECT * FROM tablelist WHERE uid = '$uid'";
        $data = MysqlUtility::MysqlQuery($sql_query);
        if(mysqli_num_rows($data) == 0){
            return array("table do not exist", 403, 'fail');
        }

        $sql_query = "DELETE FROM tablelist  WHERE uid = $uid";
        $data = MysqlUtility::MysqlQuery($sql_query);
        // return "Delete.";
        return array("Delete.", 200, 'Success');

    }
}
?>