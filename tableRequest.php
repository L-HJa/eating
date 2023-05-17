<?php

require('connectMYSQL.php');
require_once('utility.php');

class tableRequest{
    private static $method_type = array('get', 'post', 'put', 'delete');

    public static function getRequest(){
        $method = strtolower($_SERVER['REQUEST_METHOD']);
        if(in_array($method, self::$method_type)){
            $method_Func = $method.'Func';
            return self::$method_Func($_REQUEST);
        }else{ 
            // return"is not post";
            return array("傳入格式錯誤", 403, 'Fail');
        }
    }

    // Get ------------------------------------------------------------------- (未用
    // 透過指定桌子uid獲取該桌子上所有食物狀態
    private static function getFunc($request_data) {
        $body = json_decode(file_get_contents('php://input'), true);
        if(Utility::checkIsValidData(["foodInfo"], $body)) {
            $foodInfo = $body["foodInfo"];
            if($foodInfo){
                if(Utility::checkIsValidData(["tableUid"], $body))  return self::getTablesFood($request_data);
                else return self::getallTablesFood($request_data);
            }else{
                if(Utility::checkIsValidData(["tableUid"], $body))  return self::getSingleTableInfo($request_data);
                else return self::getAllTableInfo($request_data);
            }
        } else {
            return array("缺少必要資料", 401, "Fail");
        }
    }
    
    // POST 新增table -------------------------------------------------------------------
    private static function postFunc(){
        $body = json_decode(file_get_contents('php://input'), true);
        if(Utility::checkIsValidData(["merchantUid", "name", "capacity", "offset"], $body)) {
            $merchantUid = $body['merchantUid'];
            $name = $body['name'];
            $capacity = $body['capacity'];
            $offset = $body['offset'];
            $implodeOffset = implode(',', $offset);
            
            // 測試完會刪 > 假設merchant不會輸錯
            $sql_query = "SELECT * FROM merchant WHERE uid = $merchantUid";
            $data = MysqlUtility::MysqlQuery($sql_query);
            if(mysqli_num_rows($data) === 0)    return array("無此商家", 403, "Fail");
            
            // 不檢測桌名是否相同
            // $sql_query = "SELECT * FROM tablelist WHERE merchantUid = '$merchantUid' AND name = '$name'";
            // $data = MysqlUtility::MysqlQuery($sql_query);
            // if(mysqli_num_rows($data) > 0)    return array("table is exist", 403, 'fail');
            
            // insert
            $sql_query = "INSERT INTO tablelist (name, merchantUid, capacity, offset) VALUES ('$name', '$merchantUid', '$capacity', '$implodeOffset')";
            MysqlUtility::MysqlQuery($sql_query);
            // return tableUid
            $sql_query = "SELECT * FROM tablelist WHERE merchantUid = '$merchantUid' AND name = '$name'";
            $data = MysqlUtility::MysqlQuery($sql_query);
            $row = mysqli_fetch_array($data, MYSQLI_ASSOC);
            return array($row['uid'], 200, 'Success');
            // return 回傳格式 
        } else {
            array("缺少必要資料", 401, "Fail"); // ["merchantUid", "name", "capacity", "offset"]
        }
    }

    // Put 更新tsble -------------------------------------------------------------------
    // 對一張卓的資料進行修改
    private static function putFunc() {
        $body = json_decode(file_get_contents('php://input'), true);
        if(Utility::checkIsValidData(["uid", "name", "capacity", "offset"], $body)) {
            $uid = $body["uid"];
            $name = $body["name"];
            $capacity = $body["capacity"];
            $offset = implode(",", $body["offset"]);
            $sql_query = "UPDATE tablelist SET name = '$name', capacity = '$capacity', offset = '$offset' WHERE uid = '$uid'";
            $_ = MysqlUtility::MysqlQuery($sql_query);
            return array("更新成功", 200, "Success");
        } else {
            return array("缺少必要資料", 403, "Fail");
        }
    }

    // Delete -------------------------------------------------------------------
    private static function deleteFunc(){
        $body = json_decode(file_get_contents('php://input'), true);
        if(Utility::checkIsValidData(['uid'], $body)){
            $uid = $body['uid'];
            // table do not exist
            $sql_query = "SELECT * FROM tablelist WHERE uid = '$uid'";
            $data = MysqlUtility::MysqlQuery($sql_query);
            if(mysqli_num_rows($data) == 0)     return array("無此商家", 403, "Fail");
            // DELETE
            $sql_query = "DELETE FROM tablelist  WHERE uid = $uid";
            $data = MysqlUtility::MysqlQuery($sql_query);
            return array("刪除成功.", 200, 'Success');
        }else{
            return array("缺少必要資料", 401, "Fail");
        }
    }

    /*
    // 透過指定桌子uid獲取該桌子上所有食物狀態
    public static function getTablesFood(){
        $body = json_decode(file_get_contents('php://input'), true);
        if(Utility::checkIsValidData(["tableUid"], $body)) {
            $tableUid = $body["tableUid"];
            $sql_query = "SELECT * FROM food WHERE tableUid = '$tableUid'";
            $data = MysqlUtility::MysqlQuery($sql_query);
            $numberOfTable = mysqli_num_rows($data);
            $results = array();
            $cnt = 0;
            while($numberOfTable) {
                $foodInfo = mysqli_fetch_array($data, MYSQLI_ASSOC);
                $uid = $foodInfo["uid"];
                $foodType = $foodInfo["foodType"];
                $trackId = $foodInfo["trackId"];
                $foodRemain = $foodInfo["foodRemain"];
                $foodRemainTime = $foodInfo["foodRemainTime"];
                $foodRemainLine = explode(",", $foodInfo["foodRemainLine"]);
                $timeLine = explode(",", $foodInfo["timeLine"]);
                $results[$cnt] = array(
                    "uid"=> $uid, "name"=> $foodType, "trackId"=> $trackId, "foodRemain"=> $foodRemain, "foodRemainTime"=> $foodRemainTime, 
                    "foodRemainLine"=> $foodRemainLine, "timeLine"=> $timeLine
                );
                $numberOfTable -= 1;
                $cnt += 1;
            }
            return array(array("results"=> $results), 200, "Success");
        } else {
            return array("缺少必要資料", 403, "Fail");
        }
    }

    // 獲取一個商家有的所有桌子資料，包含食物資訊
    public static function getAllTablesFood(){
        return array("unfinish", 000, "unfinish");
    }

    // 獲取單張桌子的資料，不包含該桌子內部實物資料
    public static function getSingleTableInfo() {
        $body = json_decode(file_get_contents('php://input'), true);
        if(Utility::checkIsValidData(["uid"], $body)) {
            $tableUid = $body["uid"];
            $sql_query = "SELECT * FROM tablelist WHERE uid = '$tableUid'";
            $data = MysqlUtility::MysqlQuery($sql_query);
            $numberOfInfo = mysqli_num_rows($data);
            if($numberOfInfo == 0) {
                return array("查無資料,請確認桌子uid正確", 401, "Table not found");
            } elseif($numberOfInfo > 1) {
                return array("資料庫內部錯誤", 500, "Found multi table info");
            }
            $tableInfo = mysqli_fetch_array($data, MYSQLI_ASSOC);
            $name = $tableInfo["name"];
            $capacity = $tableInfo["capacity"];
            $offset = explode(",", $tableInfo["offset"]);
            $uid = $tableInfo["uid"];
            $merchantUid = $tableInfo["merchantUid"];
            $item = "table";
            $result =  array("uid"=> $uid, "name"=> $name, "capacity"=> $capacity, "offset"=> $offset, "merchantUid"=> $merchantUid, "item"=> $item);
            return array($result, 200, "Success");
        } else {
            return array("傳入格式錯誤", 403, "Fail");
        }
    }

    // 獲取一個商家有的所有桌子資料，不包含食物資訊
    public static function getAllTableInfo() {
        $body = json_decode(file_get_contents('php://input'), true);
        if(Utility::checkIsValidData(["merchantUid"], $body)) {
            $merchantUid = $body["merchantUid"];
            $sql_query = "SELECT * FROM tablelist WHERE merchantUid = '$merchantUid'";
            $data = MysqlUtility::MysqlQuery($sql_query);
            $numberOfTable = mysqli_num_rows($data);
            $results = array();
            $cnt = 0;
            while($numberOfTable) {
                $tableInfo = mysqli_fetch_array($data, MYSQLI_ASSOC);
                $name = $tableInfo["name"];
                $capacity = $tableInfo["capacity"];
                $offset = explode(",", $tableInfo["offset"]);
                $uid = $tableInfo["uid"];
                $merchantUid = $tableInfo["merchantUid"];
                $item = "table";
                $results[$cnt] = array(
                    "uid"=> $uid, "name"=> $name, "capacity"=> $capacity, "offset"=> $offset, "merchantUid"=> $merchantUid, "item"=> $item
                );
                $numberOfTable -= 1;
                $cnt += 1;
            }
            return array(array("results"=> $results), 200, "Success");
        } else {
            return array("缺少必要資料", 403, "Fail");
        }
    }

    // 獲取所有桌子以及當中的食物資訊
    public static function getAllTableWithFoodInfo() {
        $body = json_decode(file_get_contents('php://input'), true);
        if(Utility::checkIsValidData(["merchantUid"], $body)) {
            $merchantUid = $body["merchantUid"];
            $tableSqlQuery = "SELECT * FROM tablelist WHERE merchantUid = '$merchantUid'";
            $tableData = MysqlUtility::MysqlQuery($tableSqlQuery);
            $numberOfTable = mysqli_num_rows($tableData);
            $results = array();
            while($numberOfTable) {
                $tableInfo = mysqli_fetch_array($tableData, MYSQLI_ASSOC);
                $tableUid = $tableInfo["uid"];
                $tableName = $tableInfo["name"];
                $foodSqlQuery = "SELECT * FROM food WHERE tableUid = '$tableUid'";
                $foodData = MysqlUtility::MysqlQuery($foodSqlQuery);
                $numberOfFood = mysqli_num_rows($foodData);
                $cnt = 0;
                while($numberOfFood) {
                    $foodInfo = mysqli_fetch_array($foodData, MYSQLI_ASSOC);
                    $foodUid = $foodInfo["uid"];
                    $foodType = $foodInfo["foodType"];
                    $trackId = $foodInfo["trackId"];
                    $foodRemain = $foodInfo["foodRemain"];
                    $foodRemainTime = $foodInfo["foodRemainTime"];
                    $foodRemainLine = explode(",", $foodInfo["foodRemainLine"]);
                    $timeLine = explode(",", $foodInfo["timeLine"]);
                    $timeLine = array_map('intval', $timeLine);
                    $results[$tableName][$cnt] = array(
                        "uid"=> $foodUid, "name"=> $foodType, "trackId"=> $trackId, "foodRemain"=> $foodRemain, 
                        "foodRemainTime"=> $foodRemainTime, "foodRemainLine"=> $foodRemainLine, "timeLine"=> $timeLine
                    );
                    $cnt +=1;
                    $numberOfFood -= 1;
                }
                $numberOfTable -= 1;
            }
            return array(array("results"=> $results), 200, "Success");
        } else {
            return array("缺少必要資訊", 403, "Fail");
        }
    }

    // 查看是否有該桌子存在，給後端使用的
    // 文檔尚未填寫
    public static function checkTableIsExist() {
        $body = json_decode(file_get_contents('php://input'), true);
        if(Utility::checkIsValidData(["tableUid", "account"], $body)) {
            $tableUid = $body["tableUid"];
            $account = $body["account"];
            $sql_query = "SELECT * FROM merchant WHERE email = '$account'";
            $data = MysqlUtility::MysqlQuery($sql_query);
            $userInfo = mysqli_fetch_array($data, MYSQLI_ASSOC);
            $uid = $userInfo["uid"];
            $sql_query = "SELECT * FROM tablelist WHERE uid = '$tableUid' AND merchantUid = '$uid'";
            $data = MysqlUtility::MysqlQuery($sql_query);
            $numberOfTable = mysqli_num_rows($data);
            if($numberOfTable == 1) {
                return array("Success", 200, "Success");
            } else {
                return array("查無資料", 404, "Not Found Table");
            }
        } else {
            return array("缺少必要資料", 403, "Fail");
        }
    }*/

}
// (桌子, 食物)
class mercnantGetInfo{
    private static function getFoodOfTable($tableUid){
        $sql_query = "SELECT * FROM food WHERE tableUid = '$tableUid'";
        $data = MysqlUtility::MysqlQuery($sql_query);
        $numberOfTable = mysqli_num_rows($data);
        $results = array();
        $cnt = 0;
        while($numberOfTable) {
            $foodInfo = mysqli_fetch_array($data, MYSQLI_ASSOC);
            $uid = $foodInfo["uid"];
            $foodType = $foodInfo["foodType"];
            $trackId = $foodInfo["trackId"];
            $foodRemain = $foodInfo["foodRemain"];
            $foodRemainTime = $foodInfo["foodRemainTime"];
            $foodRemainLine = explode(",", $foodInfo["foodRemainLine"]);
            $timeLine = explode(",", $foodInfo["timeLine"]);
            $results[$cnt] = array(
                "uid"=> $uid, "name"=> $foodType, "trackId"=> $trackId, "foodRemain"=> $foodRemain, "foodRemainTime"=> $foodRemainTime, 
                "foodRemainLine"=> $foodRemainLine, "timeLine"=> $timeLine
            );
            $numberOfTable -= 1;
            $cnt += 1;
        }
        return array($results, 200, "Success");
    }
    // 全OR單桌 [no merchant info]
    public static function foodInfo(){
        $body = json_decode(file_get_contents('php://input'), true);
        $tables = array();
        if(Utility::checkIsValidData(["tableUid"], $body)) {
            array_push($tables, $tableUid);
        }elseif(Utility::checkIsValidData(["merchantUid"], $body)){
            $merchantUid = $body['merchantUid'];
            $table_sqlQuery = "SELECT uid FROM tablelist WHERE merchantUid = $merchantUid";
            $table_data = MysqlUtility::MysqlQuery($table_sqlQuery);
            $numOfTable = mysqli_num_rows($table_data);
            while($numOfTable){
                $table_row = mysqli_fetch_array($table_data);
                array_push($tables, $table_row['uid']);
                $numOfTable -= 1;
            }
        }else return array("缺少必要資料", 401, "Fail");
        

        $result = array();
        $cnt = 0;
        foreach($tables as $key => $value){
            $result[$cnt] = self::getFoodOfTable($value);
            echo $result[$cnt][0][0];
        }
        return array($result, 200, "Success"); 
    }

    // 透過指定桌子uid獲取該桌子上所有食物狀態 
    public static function getTablesFood(){
        $body = json_decode(file_get_contents('php://input'), true);
        if(Utility::checkIsValidData(["tableUid"], $body)) {
            $tableUid = $body["tableUid"];
            $sql_query = "SELECT * FROM food WHERE tableUid = '$tableUid'";
            $data = MysqlUtility::MysqlQuery($sql_query);
            $numberOfTable = mysqli_num_rows($data);
            $results = array();
            $cnt = 0;
            while($numberOfTable) {
                $foodInfo = mysqli_fetch_array($data, MYSQLI_ASSOC);
                $uid = $foodInfo["uid"];
                $foodType = $foodInfo["foodType"];
                $trackId = $foodInfo["trackId"];
                $foodRemain = $foodInfo["foodRemain"];
                $foodRemainTime = $foodInfo["foodRemainTime"];
                $foodRemainLine = explode(",", $foodInfo["foodRemainLine"]);
                $timeLine = explode(",", $foodInfo["timeLine"]);
                $results[$cnt] = array(
                    "uid"=> $uid, "name"=> $foodType, "trackId"=> $trackId, "foodRemain"=> $foodRemain, "foodRemainTime"=> $foodRemainTime, 
                    "foodRemainLine"=> $foodRemainLine, "timeLine"=> $timeLine
                );
                $numberOfTable -= 1;
                $cnt += 1;
            }
            return array(array("results"=> $results), 200, "Success");
        } else {
            return array("缺少必要資料", 403, "Fail");
        }
    }

    // 獲取一個商家有的所有桌子資料，包含食物資訊
    public static function getAllTablesFood(){
        return array("unfinish", 000, "unfinish");
    }

    // 獲取單張桌子的資料，不包含該桌子內部實物資料
    public static function single_table_info() {
        $body = json_decode(file_get_contents('php://input'), true);
        if(Utility::checkIsValidData(["uid"], $body)) {
            $tableUid = $body["uid"];
            $sql_query = "SELECT * FROM tablelist WHERE uid = '$tableUid'";
            $data = MysqlUtility::MysqlQuery($sql_query);
            $numberOfInfo = mysqli_num_rows($data);
            if($numberOfInfo == 0) {
                return array("查無資料,請確認桌子uid正確", 401, "Table not found");
            } elseif($numberOfInfo > 1) {
                return array("資料庫內部錯誤", 500, "Found multi table info");
            }
            $tableInfo = mysqli_fetch_array($data, MYSQLI_ASSOC);
            $name = $tableInfo["name"];
            $capacity = $tableInfo["capacity"];
            $offset = explode(",", $tableInfo["offset"]);
            $uid = $tableInfo["uid"];
            $merchantUid = $tableInfo["merchantUid"];
            $item = "table";
            $result =  array("uid"=> $uid, "name"=> $name, "capacity"=> $capacity, "offset"=> $offset, "merchantUid"=> $merchantUid, "item"=> $item);
            return array($result, 200, "Success");
        } else {
            return array("傳入格式錯誤", 403, "Fail");
        }
    }

    // 獲取一個商家有的所有桌子資料，不包含食物資訊
    public static function all_table_info() {
        $body = json_decode(file_get_contents('php://input'), true);
        if(Utility::checkIsValidData(["merchantUid"], $body)) {
            $merchantUid = $body["merchantUid"];
            $sql_query = "SELECT * FROM tablelist WHERE merchantUid = '$merchantUid'";
            $data = MysqlUtility::MysqlQuery($sql_query);
            $numberOfTable = mysqli_num_rows($data);
            $results = array();
            $cnt = 0;
            while($numberOfTable) {
                $tableInfo = mysqli_fetch_array($data, MYSQLI_ASSOC);
                $name = $tableInfo["name"];
                $capacity = $tableInfo["capacity"];
                $offset = explode(",", $tableInfo["offset"]);
                $uid = $tableInfo["uid"];
                $merchantUid = $tableInfo["merchantUid"];
                $item = "table";
                $results[$cnt] = array(
                    "uid"=> $uid, "name"=> $name, "capacity"=> $capacity, "offset"=> $offset, "merchantUid"=> $merchantUid, "item"=> $item
                );
                $numberOfTable -= 1;
                $cnt += 1;
            }
            return array(array("results"=> $results), 200, "Success");
        } else {
            return array("缺少必要資料", 403, "Fail");
        }
    }

    // 獲取所有桌子以及當中的食物資訊
    public static function all_table_with_food_info() {
        $body = json_decode(file_get_contents('php://input'), true);
        if(Utility::checkIsValidData(["merchantUid"], $body)) {
            $merchantUid = $body["merchantUid"];
            $tableSqlQuery = "SELECT * FROM tablelist WHERE merchantUid = '$merchantUid'";
            $tableData = MysqlUtility::MysqlQuery($tableSqlQuery);
            $numberOfTable = mysqli_num_rows($tableData);
            $results = array();
            while($numberOfTable) {
                $tableInfo = mysqli_fetch_array($tableData, MYSQLI_ASSOC);
                $tableUid = $tableInfo["uid"];
                $tableName = $tableInfo["name"];
                $foodSqlQuery = "SELECT * FROM food WHERE tableUid = '$tableUid'";
                $foodData = MysqlUtility::MysqlQuery($foodSqlQuery);
                $numberOfFood = mysqli_num_rows($foodData);
                $cnt = 0;
                while($numberOfFood) {
                    $foodInfo = mysqli_fetch_array($foodData, MYSQLI_ASSOC);
                    $foodUid = $foodInfo["uid"];
                    $foodType = $foodInfo["foodType"];
                    $trackId = $foodInfo["trackId"];
                    $foodRemain = $foodInfo["foodRemain"];
                    $foodRemainTime = $foodInfo["foodRemainTime"];
                    $foodRemainLine = explode(",", $foodInfo["foodRemainLine"]);
                    $timeLine = explode(",", $foodInfo["timeLine"]);
                    $timeLine = array_map('intval', $timeLine);
                    $results[$tableName][$cnt] = array(
                        "uid"=> $foodUid, "name"=> $foodType, "trackId"=> $trackId, "foodRemain"=> $foodRemain, 
                        "foodRemainTime"=> $foodRemainTime, "foodRemainLine"=> $foodRemainLine, "timeLine"=> $timeLine
                    );
                    $cnt +=1;
                    $numberOfFood -= 1;
                }
                $numberOfTable -= 1;
            }
            return array(array("results"=> $results), 200, "Success");
        } else {
            return array("缺少必要資訊", 403, "Fail");
        }
    }

    // 查看是否有該桌子存在，給後端使用的
    // 文檔尚未填寫
    public static function check_table_is_exist() {
        $body = json_decode(file_get_contents('php://input'), true);
        if(Utility::checkIsValidData(["tableUid", "account"], $body)) {
            $tableUid = $body["tableUid"];
            $account = $body["account"];
            $sql_query = "SELECT * FROM merchant WHERE email = '$account'";
            $data = MysqlUtility::MysqlQuery($sql_query);
            $userInfo = mysqli_fetch_array($data, MYSQLI_ASSOC);
            $uid = $userInfo["uid"];
            $sql_query = "SELECT * FROM tablelist WHERE uid = '$tableUid' AND merchantUid = '$uid'";
            $data = MysqlUtility::MysqlQuery($sql_query);
            $numberOfTable = mysqli_num_rows($data);
            if($numberOfTable == 1) {
                return array("Success", 200, "Success");
            } else {
                return array("查無資料", 404, "Not Found Table");
            }
        } else {
            return array("缺少必要資料", 403, "Fail");
        }
    }

    // 使用tableId來獲取商家Uid，給後端使用的
    // 文檔尚未填寫
    public static function get_merchantUid_by_table_id() {
        $body = json_decode(file_get_contents('php://input'), true);
        if(Utility::checkIsValidData(["table_id"], $body)) {
            $table_id = $body["table_id"];
            $sql_query = "SELECT * FROM tablelist WHERE uid = '$table_id'";
            $data = MysqlUtility::MysqlQuery($sql_query);
            if(mysqli_num_rows($data) != 1) {
                return array("查無資料", 404, "Not Found Table");
            }
            $merchantInfo = mysqli_fetch_array($data, MYSQLI_ASSOC);
            $merchantUid = $merchantInfo["merchantUid"];
            $result = array("merchantUid" => $merchantUid);
            return array($result, 200, "Success");
        } else {
            return array("缺少必要資料", 403, "Fail");
        }
    }

}
?>