<?php

require('connectMYSQL.php');
require_once('utility.php');

class itemRequest{
    private static $method_type = array('post', 'put', 'delete');

    public static function getRequest(){
        $method = strtolower($_SERVER['REQUEST_METHOD']);
        if(in_array($method, self::$method_type)){
            $method_Func = $method.'Func';
            return self::$method_Func($_REQUEST);
        }else{
            return array("傳入格式錯誤", 400, 'Fail');
        }
    }

    // POST                                                                                                 offset list 轉 string
    private static function postFunc(){
        $body = json_decode(file_get_contents('php://input'), true);
        if(Utility::checkIsValidData(['item', 'offset', 'merchantUid'], $body)){
            $item = $body['item'];
            $offset = $body['offset'][0].','.$body['offset'][1];
            $merchantUid = $body['merchantUid'];

            // create
            $sql_query = "INSERT INTO item (item, offset, merchantUid) VALUE ('$item', '$offset', '$merchantUid')";
            MysqlUtility::MysqlQuery($sql_query);
            
            // 回傳uid
            $sql_query = "SELECT * FROM item WHERE item = '$item' AND offset = '$offset' AND merchantUid = '$merchantUid'";
            $data = MysqlUtility::MysqlQuery($sql_query);
            $row = mysqli_fetch_array($data);
            # return array("創建成功", 200, "Success");
            return array($row['uid'], 200, "Success");
        }else{
            return array("漏填必填", 401, 'Fail');
        }
    }

    // PUT
    private static function putFunc(){
        $body = json_decode(file_get_contents('php://input'), True);
        if(Utility::checkIsValidData(['uid', 'item', 'offset', 'merchantUid'], $body)){
            $uid = $body['uid'];
            $item = $body['item'];
            $offset = $body['offset'][0].','.$body['offset'][1];
            $merchantUid = $body['merchantUid'];

            // item is not exist
            $sql_query = "SELECT * FROM item WHERE uid = '$uid'";
            $data = MysqlUtility::MysqlQuery($sql_query);
            if(mysqli_num_rows($data) == 0){
                return array("查無資料", 403, "Fail");
            }

            $sql_query = "UPDATE item SET item = '$item', offset = '$offset', merchantUid = '$merchantUid'  WHERE uid = '$uid'";
            MysqlUtility::MysqlQuery($sql_query);

            return array("更新成功", 200, "Success");
        }else{
            return array("漏填必填", 401, 'Fail');
        }
    }

    // DELETE
    private static function deleteFunc(){
        $body = json_decode(file_get_contents('php://input'), True);
        if(Utility::checkIsValidData(['uid'], $body)){
            $uid = $body['uid'];
    
            $sql_query = "SELECT * FROM item WHERE uid = '$uid'";
            $data = MysqlUtility::MysqlQuery($sql_query);
            if(mysqli_num_rows($data) == 0){
                return array("該物件不存在", 403, "Input Wrong");
            }

            $sql_query = "DELETE FROM item WHERE uid = '$uid'";
            MysqlUtility::MysqlQuery($sql_query);

            return array("刪除成功", 200, "Success");
        }else{
            return array("漏填必填", 401, 'Fail');
        }
    }



    // 獲得店家所有item
    public static function getAllItem(){
        $body = json_decode(file_get_contents('php://input'), True);
        if(Utility::checkIsValidData(['merchantUid'], $body)){
            $merchantUid = $body['merchantUid'];

            $sql_query = "SELECT * FROM merchant WHERE uid = '$merchantUid'";
            $data = MysqlUtility::MysqlQuery($sql_query);
            if(mysqli_num_rows($data) != 0){
                $sql_query = "SELECT * FROM item WHERE merchantUid = '$merchantUid'";
                $data = MysqlUtility::MysqlQuery($sql_query);

                $cnt = 0;
                $result = array();
                $numOfData = mysqli_num_rows($data);
                if($numOfData != 0){
                    while($numOfData){
                        $row = mysqli_fetch_array($data);
                        $uid = $row['uid'];
                        $item = $row['item'];
                        $offset = explode(',', $row['offset']);   

                        $result[$cnt] = array(
                            "uid" => $uid,
                            "item" => $item,
                            "offset" => $offset,
                            "merchantUid" => $merchantUid
                        );

                        $numOfData--;
                        $cnt++;
                    }
                    return array($result, 200, "Success");
                }else{
                    return array("該店無物件資訊", 200, "Fail");
                }
            }else{
                return array("無此店家", 403, "Fail");
            }
        }else{
            return array("缺少必要資料", 403, "Fail");
        }
    }
}

?>