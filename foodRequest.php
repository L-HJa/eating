<?php

require('connectMYSQL.php');

class tableRequest{
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
        if(!(empty($body['foodtype']) || empty($body['trackId']) || empty($body['foodRemain']) || empty($body['foodRemainTime']) || empty($body['foodRemainLine']) || empty($body['timeLine']) || empty($body['tableUid']))){
            $foodtype = $body['foodtype'];
            $trackId = $body['trackId'];
            $foodRemain = $body['foodRemain'];
            $foodRemainTime = $body['foodRemainTime'];
            $foodRemainLine = $body['foodRemainLine'];
            $timeLine = $body['timeLine'];
            $tableUid = $body['tableUid'];
            
            $sql_query = "INSERT INTO "."food"." (foodtype, trackId, foodRemain, foodRemainTime, foodRemainLine, timeLine, tableUid) VALUES ('$foodtype', '$trackId', '$foodRemain', '$foodRemainTime', '$foodRemainLine', '$timeLine', '$tableUid')";
            MysqlUtility::MysqlQuery($sql_query);

            $sql_query = "SELECT * FROM table WHERE trackId = '$trackId' AND tableUid = '$tableUid'";
            $data = MysqlUtility::MysqlQuery($sql_query);
            $row = mysqli_fetch_array($data, MYSQLI_ASSOC);
            return array($row['uid'], 200, 'Success');
            // return 回傳格式 
           
        }
    }

    // PUT Update
    private static function putFunc(){
        $body = json_decode(file_get_contents('php://input'), true);
        # parse_str(json_decode(file_get_contents('php://input'), true), $body);
        if(!(empty('uid') || empty($body['foodtype']) || empty($body['trackId']) || empty($body['foodRemain']) || empty($body['foodRemainTime']) || empty($body['foodRemainLine']) || empty($body['timeLine']) || empty($body['tableUid']))){
            $uid = $body['uid'];
            $foodtype = $body['foodtype'];
            $trackId = $body['trackId'];
            $foodRemain = $body['foodRemain'];
            $foodRemainTime = $body['foodRemainTime'];
            $foodRemainLine = $body['foodRemainLine'];
            $timeLine = $body['timeLine'];
            $tableUid = $body['tableUid'];

            $sql_query = "UPDATE food SET foodtype = '$foodtype', trackId = '$trackId', foodRemain = '$foodRemain', foodRemainTime = '$foodRemainTime', foodRemainLine = '$foodRemainLine', timeLine = '$timeLine', tableUid = '$tableUid' WHERE uid = '$uid'";
            MysqlUtility::MysqlQuery($sql_query);
            return array("Update.", 200, 'Success');    

        }else{
            return array("漏填必填", 401, 'FailLogin');
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