<?php

require('connectMYSQL.php');

class foodRequest{
    private static $method_type = array('post', 'put', 'delete'); 

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
        if(!(empty($body['foodType']) || empty($body['trackId']) || empty($body['foodRemain']) || empty($body['foodRemainTime']) || empty($body['foodRemainLine']) || empty($body['timeLine']) || empty($body['tableUid']))){
            $foodType = $body['foodType'];
            $trackId = $body['trackId'];
            $foodRemain = $body['foodRemain'];
            $foodRemainTime = $body['foodRemainTime'];
            $foodRemainLine = $body['foodRemainLine'];
            $timeLine = $body['timeLine'];
            $tableUid = $body['tableUid'];
            
            $sql_query = "SELECT * FROM food WHERE trackId = '$trackId' AND tableUid = '$tableUid'";
            $data = MysqlUtility::MysqlQuery($sql_query);
            if(mysqli_num_rows($data) > 0){
                return array("food exist", 403, "fail");
            }

            $sql_query = "INSERT INTO "."food"." (foodType, trackId, foodRemain, foodRemainTime, foodRemainLine, timeLine, tableUid) VALUES ('$foodType', '$trackId', '$foodRemain', '$foodRemainTime', '$foodRemainLine', '$timeLine', '$tableUid')";
            MysqlUtility::MysqlQuery($sql_query);

            $sql_query = "SELECT * FROM food WHERE trackId = '$trackId' AND tableUid = '$tableUid'";
            $data = MysqlUtility::MysqlQuery($sql_query);
            $row = mysqli_fetch_array($data, MYSQLI_ASSOC);
            return array($row['uid'], 200, 'Success');
            // return 回傳格式  
        }else{
            return array("漏填必填", 401, 'Fail');
        }
    }

    // PUT Update
    private static function putFunc(){
        $body = json_decode(file_get_contents('php://input'), true);
        # parse_str(json_decode(file_get_contents('php://input'), true), $body);
        if(!(empty($body['uid']) || empty($body['foodType']) || empty($body['trackId']) || empty($body['foodRemain']) || empty($body['foodRemainTime']) || empty($body['foodRemainLine']) || empty($body['timeLine']) || empty($body['tableUid']))){
            $uid = $body['uid'];
            $foodType = $body['foodType'];
            $trackId = $body['trackId'];
            $foodRemain = $body['foodRemain'];
            $foodRemainTime = $body['foodRemainTime'];
            $foodRemainLine = $body['foodRemainLine'];
            $timeLine = $body['timeLine'];
            $tableUid = $body['tableUid'];

            $sql_query = "SELECT * FROM food WHERE trackId = '$trackId' AND tableUid = '$tableUid'";
            $data = MysqlUtility::MysqlQuery($sql_query);
            if(mysqli_num_rows($data) > 0){
                return array("food do not exist", 403, "fail");
            }

            $sql_query = "UPDATE food SET foodType = '$foodType', trackId = '$trackId', foodRemain = '$foodRemain', foodRemainTime = '$foodRemainTime', foodRemainLine = '$foodRemainLine', timeLine = '$timeLine', tableUid = '$tableUid' WHERE uid = '$uid'";
            MysqlUtility::MysqlQuery($sql_query);
            return array("Update.", 200, 'Success');    

        }else{
            return array("漏填必填", 401, 'Fail');
        }
        

    } 

    // Delete
    private static function deleteFunc(){
        $body = json_decode(file_get_contents('php://input'), true);
        if(! empty($body['uid'])){
            $uid = $body['uid'];

            // food do not exist
            $sql_query = "SELECT * FROM food WHERE uid = '$uid'";
            $data = MysqlUtility::MysqlQuery($sql_query);
            if(mysqli_num_rows($data) == 0){
                return array("table do not exist", 403, 'Fail');
            }

            $sql_query = "DELETE FROM food WHERE uid = $uid";
            $data = MysqlUtility::MysqlQuery($sql_query);
            // return "Delete.";
            return array("Delete.", 200, 'Success');
        }else{
            return array("漏填必填", 401, 'FailLogin');
        }
        

    }
}
?>