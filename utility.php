<?php

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
}