<?php

require('connectMYSQL.php');
require_once('utility.php');

class foodRequest{
    private static $method_type = array('post', 'put', 'delete', 'get'); 

    public static function getRequest(){
        $method = strtolower($_SERVER['REQUEST_METHOD']);
        if(in_array($method, self::$method_type)){
            $method_Func = $method.'Func';
            return self::$method_Func($_REQUEST);
        }else{ 
            return array("傳入格式錯誤", 400, 'FailSignin');
        }
    }

    // POST -------------------------------------------------------------------
    private static function postFunc(){
        $body = json_decode(file_get_contents('php://input'), true);
        if(Utility::checkIsValidData(['foodType', 'trackId', 'foodRemain', 'foodRemainTime', 'foodRemainLine', 'timeLine', 'tableUid'], $body)) {
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
        if(Utility::checkIsValidData(["tableUid", "trackId"], $body)) {
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
        if(Utility::checkIsValidData(["foodType", "trackId", "foodRemain", "foodRemainTime", "foodRemainLine", "timeLine", "tableUid"], $body)) {
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
        // 直接使用tableUid以及trackId來刪除，這樣模型端就可以不用記錄下food的uid
        if(Utility::checkIsValidData(["tableUid", "trackId"], $body)) {
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
            return array("刪除.", 200, "Success");
        } elseif(!(empty($body['tableUid']) || empty($body['deleteAll']))) {
            $tableUid = $body['tableUid'];

            $sql_query = "DELETE FROM food WHERE tableUid = '$tableUid'";
            $_ = MysqlUtility::MysqlQuery($sql_query);
            return array("全部刪除", 200, "Success");
        } else {
            return array("漏填必填", 401, "Fail Delete");
        }
    }

}

// Customer ------------------------------------------------------------------------------------------
class customerGetFoodInfo{
    private static $method_type = array('post'); 

    public static function getRequest(){
        $method = strtolower($_SERVER['REQUEST_METHOD']);
        if(in_array($method, self::$method_type)){
            $method_Func = $method.'Func';
            return self::$method_Func($_REQUEST);
        }else{ 
            return array("傳入格式錯誤", 400, 'Fail');
        }
    }
    // 指定店家所有桌子(回傳每桌foodRemainTime最多的數值) ->        0:空桌, num: 剩餘時間        [未測試]
    private static function postFunc(){
        $body = json_decode(file_get_contents('php://input'), True);
        if(Utility::checkIsValidData(['merchantUid'], $body)){
            $merchantUid = $body['merchantUid'];
            $results = array();

            $merchant_sqlQuery = "SELECT * FROM merchant WHERE uid = '$merchantUid'";
            $merchant_data = MysqlUtility::MysqlQuery($merchant_sqlQuery);
            $merchant_row = mysqli_fetch_array($merchant_data, MYSQLI_ASSOC);
            if($merchant_row == 0)    return array("無此商家", 403, "Fail");
            $results['merchantName'] = $merchant_row['name'];
            $results['merchantUid'] = $merchantUid;

            $table_sqlQuery = "SELECT * FROM tablelist WHERE merchantUid = '$merchantUid' ORDER BY name";
            $table_data = MysqlUtility::MysqlQuery($table_sqlQuery);
            $table_nums = mysqli_num_rows($table_data);
            if($table_nums == 0)    return array("此商家尚未準備好", 403, "Fail");
            $cnt = 0;
            while($table_nums != 0){
                $table_row = mysqli_fetch_array($table_data, MYSQLI_ASSOC);
                $tableUid = $table_row['uid'];
                $tableName = $table_row['name'];
                // foodRemainTime 最大值
                $food_sqlQuery = "SELECT * FROM food WHERE tableUid = '$tableUid' ORDER BY foodRemainTime DESC";
                $food_data = MysqlUtility::MysqlQuery($food_sqlQuery);
                $food_row = mysqli_fetch_array($food_data, MYSQLI_ASSOC);
                if(mysqli_num_rows($food_data) == 0){
                    $results['remainTime'][$cnt] = array("tableName" => $tableName, "remainTime" => "0");
                }else{
                    $results['remainTime'][$cnt] = array("tableName" => $tableName, "remainTime" => $food_row['foodRemainTime']);
                }$table_nums -= 1;
                $cnt += 1;
            }
            return array($results, 200, "Success");
        }else{
            return array("缺少必要資訊", 401, "Fail");
        }
    }
}
?>