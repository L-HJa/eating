<?php

require_once('connectMYSQL.php');

class Utility {

    // 檢查必要資料是否都有傳入
    static public function checkIsValidData($requiresInfo, $inputData) {
        foreach($requiresInfo as $requireInfo) {
            if(!array_key_exists($requireInfo, $inputData)) {
                return false;
            }
        }
        return true;
    }

    // 將傳送過來的圖像從base64的字串轉成image
    static public function base64ImageTransform($data) {
        $imageData = base64_decode($data);
        return $imageData;
    }

    // 檢查是否存在此商家
    static public function checkIsMerchantExist($uid) {
        $sql_query = "SELECT * FROM merchant WHERE uid = '$uid'";
        $data = MysqlUtility::MysqlQuery($sql_query);
        return mysqli_num_rows($data) == 1;
    }
}