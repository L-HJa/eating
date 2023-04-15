<?php

require('connectMYSQL.php');

class foodRequest{
    private static $method_type = array('post', 'put', 'delete', 'get'); 

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

            // 這裡先檢查桌子是否存在，否則會在下面的查詢會直接報錯
            $sql_query = "select * from tablelist where uid = '$tableUid'";
            $data = MysqlUtility::MysqlQuery($sql_query);
            if(mysqli_num_rows($data) != 1) {
                return array("table is not exist", 405, "fail");
            }
            
            $sql_query = "SELECT * FROM food WHERE trackId = '$trackId' AND tableUid = '$tableUid'";
            $data = MysqlUtility::MysqlQuery($sql_query);
            if(mysqli_num_rows($data) > 0){
                return array("food exist", 403, "fail");
            }

            // 傳入的foodRemainLine與timeLine皆為array格式，用逗號進行分隔轉換成string格式
            $implodeFoodRemainLine = implode(',', $foodRemainLine);
            $implodeTimeLine = implode(',', $timeLine);

            $sql_query = "INSERT INTO "."food"." (foodType, trackId, foodRemain, foodRemainTime, foodRemainLine, timeLine, tableUid) 
            VALUES ('$foodType', '$trackId', '$foodRemain', '$foodRemainTime', '$implodeFoodRemainLine', '$implodeTimeLine', '$tableUid')";
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

    // GET Info
    private static function getFunc() {
        $body = json_decode(file_get_contents('php://input'), true);
        if((empty($body["tableUid"]) || empty($body["trackId"]))) {
            return array("漏填必填", 401, 'Fail');
        }

        $tableUid = $body["tableUid"];
        $trackId = $body["trackId"];
        
        $sql_query = "SELECT * FROM food WHERE trackId = '$trackId' AND tableUid = '$tableUid'";
        $data = MysqlUtility::MysqlQuery($sql_query);
        if(mysqli_num_rows($data) == 0) {
            return array("food do not exist", 403, "Fail");
        } elseif(mysqli_num_rows($data) != 1) {
            return array("System error: More then one results", 500, "Fail");
        }

        $info = mysqli_fetch_array($data, MYSQLI_ASSOC);
        
        $info["foodRemainLine"] = explode(",", $info["foodRemainLine"]);
        $info["timeLine"] = explode(",", $info["timeLine"]);

        return array($info, 200, $tableUid);
    }

    // PUT Update
    private static function putFunc(){
        $body = json_decode(file_get_contents('php://input'), true);
        # parse_str(json_decode(file_get_contents('php://input'), true), $body);
        
        // 這裡的uid似乎沒有作用，請確認一下，如果真實沒用請刪除
        if(!(empty($body['foodType']) || empty($body['trackId']) || empty($body['foodRemain']) || empty($body['foodRemainTime']) || empty($body['foodRemainLine']) || empty($body['timeLine']) || empty($body['tableUid']))){
            // $uid = $body['uid'];
            $foodType = $body['foodType'];
            $trackId = $body['trackId'];
            $foodRemain = $body['foodRemain'];
            $foodRemainTime = $body['foodRemainTime'];
            $foodRemainLine = $body['foodRemainLine'];
            $timeLine = $body['timeLine'];
            $tableUid = $body['tableUid'];

            $sql_query = "SELECT * FROM food WHERE trackId = '$trackId' AND tableUid = '$tableUid'";
            $data = MysqlUtility::MysqlQuery($sql_query);
            if(mysqli_num_rows($data) == 0){
                return array("food do not exist", 403, "fail");
            } elseif(mysqli_num_rows($data) != 1) {
                return array("system error: more then one food", 500, 'fail');
            }

            // 傳入的foodRemainLine與timeLine皆為array格式，用逗號進行分隔轉換成string格式
            $implodeFoodRemainLine = implode(',', $foodRemainLine);
            $implodeTimeLine = implode(',', $timeLine);

            $sql_query = "UPDATE food SET foodType = '$foodType', foodRemain = '$foodRemain', foodRemainTime = '$foodRemainTime', foodRemainLine = '$implodeFoodRemainLine', timeLine = '$implodeTimeLine' WHERE trackId = '$trackId' AND tableUid = '$tableUid'";
            MysqlUtility::MysqlQuery($sql_query);
            return array("Update.", 200, 'Success');    

        }else{
            return array("漏填必填", 401, 'Fail');
        }
    } 

    // Delete
    private static function deleteFunc(){
        $body = json_decode(file_get_contents('php://input'), true);
        // 這裡可以視情況改成使用桌子uid以及trackId來刪除
        // if(! empty($body['uid'])){
        //     $uid = $body['uid'];

        //     // food do not exist
        //     $sql_query = "SELECT * FROM food WHERE uid = '$uid'";
        //     $data = MysqlUtility::MysqlQuery($sql_query);
        //     if(mysqli_num_rows($data) == 0){
        //         return array("table do not exist", 403, 'Fail');
        //     }

        //     $sql_query = "DELETE FROM food WHERE uid = $uid";
        //     $data = MysqlUtility::MysqlQuery($sql_query);
        //     // return "Delete.";
        //     return array("Delete.", 200, 'Success');
        // }else{
        //     return array("漏填必填", 401, 'FailLogin');
        // }

        // 直接使用tableUid以及trackId來刪除，這樣模型端就可以不用記錄下food的uid
        if(!(empty($body['tableUid']) || empty($body['trackId']))) {
            $tableUid = $body['tableUid'];
            $trackId = $body['trackId'];

            $sql_query = "select * from food where tableUid = '$tableUid' and trackId = '$trackId'";
            $data = MysqlUtility::MysqlQuery($sql_query);
            if(mysqli_num_rows($data) == 0) {
                return array("food is not exist", 403, "Fail");
            } elseif(mysqli_num_rows($data) != 1) {
                return array("System error, found more then one food", 500, "Fail");
            }
            $sql_query = "delete from food where tableUid = '$tableUid' and trackId = '$trackId'";
            $_ = MysqlUtility::MysqlQuery($sql_query);
            return array("Delete.", 200, "Success");
        } elseif(!(empty($body['tableUid']) || empty($body['deleteAll']))) {
            $tableUid = $body['tableUid'];

            $sql_query = "DELETE FROM food WHERE tableUid = '$tableUid'";
            $_ = MysqlUtility::MysqlQuery($sql_query);
            return array("Delete All", 200, "Success");
        } else {
            return array("漏填必填", 401, "Fail Delete");
        }
    }
}
?>