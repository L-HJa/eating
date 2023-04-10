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
        if (! (empty($body['uid']) || empty($body['name']))) {
            $merchant = $body['uid'];
            $name = $body['name'];
            
            $sql_query = "INSERT INTO "."table"." (name) VALUES ('$name')";
            MysqlUtility::MysqlQuery($sql_query);

            $sql_query = "SELECT * FROM table WHERE uid = '$merchant' AND name = '$name'";
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
        $sql_query = "DELETE FROM table  WHERE uid = $uid";
        $data = MysqlUtility::MysqlQuery($sql_query);
        // return "Delete.";
        return array("Delete.", 200, 'Success');

    }
}
?>