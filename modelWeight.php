<?php

require_once('connectMYSQL.php');
require_once('utility.php');

class modelWeight {

    // 支援的食物類型以及資料類別
    private static $supportFoodType = array("Donburi", "SoupRice", "Rice", "Countable", "SoupNoodle", "Noodle", "SideDish", "SolidSoup", "Soup");
    private static $supportDataType = array("ObjectDetection", "Segmentation");

    private static $storageRoot = "D:/Storage";

    // 上傳目標檢測訓練資料
    static public function uploadObjectDetectionSingleImage() {
        $body = json_decode(file_get_contents('php://input'), true);
        if(Utility::checkIsValidData(["uid", "image", "labelInfo"], $body)) {
            // uid = 使用者id
            // image = 圖像資料
            // labelInfo = 標註資料
            $uid = $body["uid"];
            $imageBase64 = $body["image"];
            $labelInfo = $body["labelInfo"];
            $image = Utility::base64ImageTransform($imageBase64);

            // 檢查是否有無此店家
            if(!Utility::checkIsMerchantExist($uid)) {
                return array("查無此商家", 404, "Fail");
            }
            self::saveObjectDetectionTrainData($uid, $image, $labelInfo);
            return array("Success", 200, "Success");
        } else {
            return array("缺少必要資料", 403, "Fail");
        }
    }

    // 保存目標檢測訓練圖像以及標註資料
    static private function saveObjectDetectionTrainData($uid, $image, $labelInfo) {
        $folderPath = self::$storageRoot."/$uid";
        if(!file_exists($folderPath)) {
            mkdir($folderPath);
        }
        $folderPath = $folderPath."/ObjectDetection";
        if(!file_exists($folderPath)) {
            mkdir($folderPath);
        }
        if(!file_exists($folderPath."/imgs")) {
            mkdir($folderPath."/imgs");
        }
        if(!file_exists($folderPath."/annotations")) {
            mkdir($folderPath."/annotations");
        }
        $fileName = uniqid();
        $saveImagePath = $folderPath."/imgs"."/".$fileName.".jpg";
        file_put_contents($saveImagePath, $image);
        $labelJson = json_encode($labelInfo);
        $saveAnnotationPath = $folderPath."/annotations"."/".$fileName.".txt";
        file_put_contents($saveAnnotationPath, $labelInfo);
    }

    // 訓練目標檢測模型
    static public function trainObjectDetectionModel() {
        $body = json_decode(file_get_contents('php://input'), true);
        if(Utility::checkIsValidData(["uid"], $body)) {
            $uid = $body["uid"];
            $root = self::$storageRoot;

            if(!Utility::checkIsMerchantExist($uid)) {
                return array("商家不存在", 404, "Fail");
            }

            $commend = "C:/Anaconda/envs/pytorch/python.exe python/GenerateYoloXTrainData.py $uid $root";
            exec($commend, $out);
            return array($out, 200, "Success");
        } else {
            return array("缺少必要資料", 403, "Fail");
        }
    }
}