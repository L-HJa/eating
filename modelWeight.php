<?php

require_once('connectMYSQL.php');
require_once('utility.php');

class objectDetectionModelWeight {

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
        if(Utility::checkIsValidData(["merchantUid"], $body)) {
            $uid = $body["merchantUid"];
            $root = self::$storageRoot;

            if(!Utility::checkIsMerchantExist($uid)) {
                return array("商家不存在", 404, "Fail");
            }

            $sql_query = "SELECT * FROM train WHERE merchantUid = '$uid'";
            $data = MysqlUtility::MysqlQuery($sql_query);
            if(mysqli_num_rows($data) > 0) {
                $trainInfo = mysqli_fetch_array($data, MYSQLI_ASSOC);
                $trainType = $trainInfo["trainType"];
                $startTime = $trainInfo["startTime"];
                $result = [
                    "status" => "Reject",
                    "trainType" => $trainType,
                    "startTime" => $startTime
                ];
                return array($result, 201, "Reject");
            } else {
                $sql_query = "INSERT INTO train (merchantUid, trainType) VALUES ('$uid', '第一階段')";
                MysqlUtility::MysqlQuery($sql_query);
            }

            $commend = "python python/ObjectDetection/YoloXTrain.py $uid $root";
            exec($commend, $out);
            return array($out, 200, "Success");
        } else {
            return array("缺少必要資料", 403, "Fail");
        }
    }

    // 查詢是否正在訓練
    static public function checkIsTrain() {
        $body = json_decode(file_get_contents('php://input'), true);
        if(Utility::checkIsValidData(["merchantUid"], $body)) {
            $uid = $body["merchantUid"];
            $sql_query = "SELECT * FROM train WHERE merchantUid = '$uid'";
            $data = MysqlUtility::MysqlQuery($sql_query);
            if(mysqli_num_rows($data) > 0) {
                $trainInfo = mysqli_fetch_array($data, MYSQLI_ASSOC);
                $trainType = $trainInfo["trainType"];
                $startTime = $trainInfo["startTime"];
                $result = [
                    "status" => "Training",
                    "trainType" => $trainType,
                    "startTime" => $startTime
                ];
                return array($result, 200, "Training");
            } else {
                return array("無正在訓練", 201, "No training");
            }
        } else {
            return array("缺少必要資訊", 403, "Fail");
        }
    }

    // 將訓練的python執行pid保存
    static public function savePythonPid() {
        $body = json_decode(file_get_contents('php://input'), true);
        if(Utility::checkIsValidData(["uid", "pid"], $body)) {
            $uid = $body["uid"];
            $pid = $body["pid"];
            $sql_query = "UPDATE train SET pid = '$pid' WHERE merchantUid = '$uid'";
            $_ = MysqlUtility::MysqlQuery($sql_query);
            return array("Success", 200, "Success");
        } else {
            return array("缺少資料", 403, "Fail");
        }
    }

    // 移除正在訓練的狀態
    static public function deleteTrainStatus() {
        $body = json_decode(file_get_contents('php://input'), true);
        if(Utility::checkIsValidData(["uid"], $body)) {
            $uid = $body["uid"];
            $sql_query = "DELETE FROM train WHERE merchantUid = '$uid'";
            $_ = MysqlUtility::MysqlQuery($sql_query);
            return array("Success", 200, "Success");
        } else {
            return array("缺少必要資料", 403, "Fail");
        }
    }

    // 取消訓練
    static public function stopTrain() {
        $body = json_decode(file_get_contents('php://input'), true);
        if(Utility::checkIsValidData(["merchantUid"], $body)) {
            $uid = $body["merchantUid"];
            $sql_query = "SELECT * FROM train WHERE merchantUid = '$uid'";
            $data = MysqlUtility::MysqlQuery($sql_query);
            $trainInfo = mysqli_fetch_array($data, MYSQLI_ASSOC);
            $pid = $trainInfo["pid"];
            if($pid == 0) {
                return array("請稍後再試，目前尚未開始", 201, "Wait");
            }
            $commend = "taskkill /pid $pid /f";
            exec($commend, $out);
            $sql_query = "DELETE FROM train WHERE merchantUid = '$uid'";
            $_ = MysqlUtility::MysqlQuery($sql_query);
            return array("Success", 200, "Success");
        } else {
            return array("缺少必要資料", 403, "Fail");
        }
    }

    // 獲取總共有多少訓練圖像
    static public function trainImageCount() {
        $body = json_decode(file_get_contents('php://input'), true);
        if(Utility::checkIsValidData(["merchantUid", "dataType"], $body)) {
            $uid = $body["merchantUid"];
            $dataType = $body["dataType"];
            if(!Utility::checkIsMerchantExist($uid)) {
                return array("商家不存在", 404, "Fail");
            }
            if(!in_array($dataType, self::$supportDataType)) {
                return array("Not Support Data Type", 500, "Not Support");
            }
            if($dataType == "ObjectDetection") {
                $dir = self::$storageRoot."/$uid"."/$dataType"."/imgs";
            } else {
                if(Utility::checkIsValidData(["foodType"], $body)) {
                    $foodType = $body["foodType"];
                    if(!in_array($foodType, self::$supportFoodType)) {
                        return array("Not Support Food Type", 500, "Not Support");
                    }
                    $dir = self::$storageRoot."/$uid"."/$dataType"."/$foodType"."/imgs";
                } else {
                    return array("缺少foodType資料", 405, "Fail");
                }
            }
            if(is_dir($dir)) {
                $folderInfo = scandir($dir);
                $fileCount = strval(count($folderInfo) - 2);
                if($fileCount == 0) {
                    return array("無訓練資料", 201, "No train data on server");
                }
                return array($fileCount, 200, "Success");
            } else {
                return array("0", 200, "Success");
            }
        } else {
            return array("缺少必要資料", 403, "Fail");
        }
    }

    // 獲取總共有多少訓練圖像
    static public function objectDetectionTrainImageCount() {
        $body = json_decode(file_get_contents('php://input'), true);
        if(Utility::checkIsValidData(["merchantUid"], $body)) {
            $uid = $body["merchantUid"];
            if(!Utility::checkIsMerchantExist($uid)) {
                return array("商家不存在", 404, "Fail");
            }
            $dir = self::$storageRoot."/$uid"."/ObjectDetection"."/imgs";
            if(is_dir($dir)) {
                $folderInfo = scandir($dir);
                $fileCount = strval(count($folderInfo) - 2);
                if($fileCount == 0) {
                    return array("無訓練資料", 201, "No train data on server");
                }
                return array($fileCount, 200, "Success");
            } else {
                return array("資料夾狀態錯誤", 500, "Fail");
            }
        } else {
            return array("缺少必要資料", 403, "Fail");
        }
    }

    // 獲取已上傳的圖像資料
    static public function objectDetectionTrainImageInfo() {
        $body = json_decode(file_get_contents('php://input'), true);
        if(Utility::checkIsValidData(["merchantUid", "page", "gap"], $body)) {
            $uid = $body["merchantUid"];
            $page = $body["page"];
            $gap = $body["gap"];
            if(!Utility::checkIsMerchantExist($uid)) {
                return array("商家不存在", 404, "Fail");
            } elseif($page < 1) {
                return array("查詢頁面不可小於1", 401, "Fail");
            }
            $rootDir = self::$storageRoot."/$uid"."/ObjectDetection";
            $imageDir = $rootDir."/imgs";
            $annoDir = $rootDir."/annotations";
            $imageFolderInfo = scandir($imageDir);
            $annoFolderInfo = scandir($annoDir);
            $imageFolderInfo = array_slice($imageFolderInfo, 2);
            $annoFolderInfo = array_slice($annoFolderInfo, 2);
            sort($imageFolderInfo);
            sort($annoFolderInfo);
            $targetImageFilesName = array_slice($imageFolderInfo, $gap * ($page - 1), $gap);
            $targetAnnoFilesName = array_slice($annoFolderInfo, $gap * ($page - 1), $gap);
            $fileCount = strval(count($targetImageFilesName));
            $result = array();
            for($i=0;$i<$fileCount;$i++) {
                $imagePath = $rootDir."/imgs"."/$targetImageFilesName[$i]";
                $annoPath = $rootDir."/annotations"."/$targetAnnoFilesName[$i]";
                $imageData = file_get_contents($imagePath);
                $base64 = base64_encode($imageData);
                $result[$i]["image"] = $base64;
                $annoData = file_get_contents($annoPath);
                $result[$i]["anno"] = $annoData;
                $result[$i]["target"] = substr($targetImageFilesName[$i], 0, -4);
            }
            if($fileCount == 0) {
                return array("無訓練資料", 201, "No train data on server");
            }
            return array($result, 200, "Success");
        } else {
            return array("缺少必要資料", 403, "Fail");
        }
    }

    // 移除目標檢測訓練圖像資料
    static public function deleteObjectDetectionTrainImage() {
        $body = json_decode(file_get_contents('php://input'), true);
        if(Utility::checkIsValidData(["merchantUid", "target"], $body)) {
            $uid = $body["merchantUid"];
            $target = $body["target"];

            $rootPath = self::$storageRoot."/$uid"."/ObjectDetection";
            $imagePath = $rootPath."/imgs"."/$target.jpg";
            $annoPath = $rootPath."/annotations"."/$target.txt";
            if(file_exists($imagePath)) {
                unlink($imagePath);
            } else {
                return array("檔案不存在", 404, "Not Found Data");
            }
            if(file_exists($annoPath)) {
                unlink($annoPath);
            } else {
                return array("檔案不存在", 404, "Not Found Data");
            }
            return array("Success", 200, "Success");
        } else {
            return array("缺少必要資料", 403, "Fail");
        }
    }

    // 檢查圖像資料數量足夠
    static private function checkPhotoIsManyEnough() {

    }

    // 獲取目標檢測的模型資料
    static public function fetchObjectDetectionModelInfo() {
        $body = json_decode(file_get_contents('php://input'), true);
        if(Utility::checkIsValidData(["merchantUid"], $body)) {
            $uid = $body["merchantUid"];
            $rootPath = self::$storageRoot."/$uid"."/ObjectDetectionWeight";

            $sql_query = "SELECT * FROM objectDetectionModelSelect WHERE merchantUid = '$uid'";
            $data = MysqlUtility::MysqlQuery($sql_query);
            $selectFileName = "";
            if(mysqli_num_rows($data) > 0) {
                $trainModelSelectInfo = mysqli_fetch_array($data, MYSQLI_ASSOC);
                $selectFileName = $trainModelSelectInfo["modelName"];
            }

            if(is_dir($rootPath)) {
                $modelFiles = scandir($rootPath);
                $modelFiles = array_slice($modelFiles, 2);
                $modelCount = strval(count($modelFiles));
                $result = array();
                for($i=0;$i<$modelCount;$i++) {
                    $fileName = explode(".", $modelFiles[$i])[0];
                    $result[$i]["name"] = explode("_", $fileName)[0];
                    $result[$i]["recommend"] = explode("_", $fileName)[1];
                    if($fileName == $selectFileName) {
                        $result[$i]["choose"] = 1;
                    } else {
                        $result[$i]["choose"] = 0;
                    }
                }
                return array($result, 200, "Success");
            } else {
                return array("無自定義權重", 201, "Success");
            }
        } else {
            return array("缺少必要資料", 403, "Fail");
        }
    }

    // 更新模型名稱
    static public function changeObjectDetectionWeightName() {
        $body = json_decode(file_get_contents('php://input'), true);
        if(Utility::checkIsValidData(["merchantUid", "oldName", "newName", "recommend"], $body)) {
            $uid = $body["merchantUid"];
            $oldName = $body["oldName"];
            $newName = $body["newName"];
            $recommend = $body["recommend"];
            $oldName = $oldName."_".$recommend;
            $newName = $newName."_".$recommend;

            $rootPath = self::$storageRoot."/$uid"."/ObjectDetectionWeight";
            $oldFilePath = $rootPath."/$oldName".".pth";
            if(!is_file($oldFilePath)) {
                return array("查無權重資料", 404, "Not Found Weight File");
            }
            $newFilePath = $rootPath."/$newName".".pth";
            rename($oldFilePath, $newFilePath);
            return array("Success", 200, "Success");
        } else {
            return array("缺少必要資料", 403, "Fail");
        }
    }

    // 設定商家選擇的權重
    static public function objectDetectionModelSelected() {
        $body = json_decode(file_get_contents('php://input'), true);
        if(Utility::checkIsValidData(["merchantUid", "fileName", "recommend", "reset"], $body)) {
            $uid = $body["merchantUid"];
            $fileName = $body["fileName"];
            $recommend = $body["recommend"];
            $reset = $body["reset"];
            $fileName = $fileName."_".$recommend;

            if($reset == 1) {
                $sql_query = "DELETE FROM objectDetectionModelSelect  WHERE merchantUid = $uid";
                $_ = MysqlUtility::MysqlQuery($sql_query);
                return array("Success", 200, "Success");
            }
            $sql_query = "SELECT * FROM objectDetectionModelSelect WHERE merchantUid = '$uid'";
            $data = MysqlUtility::MysqlQuery($sql_query);
            if(mysqli_num_rows($data) > 0) {
                // 更新資料庫內容
                $sql_query = "UPDATE objectDetectionModelSelect SET modelName = '$fileName' WHERE merchantUid = '$uid'";
                $_ = MysqlUtility::MysqlQuery($sql_query);
            } else {
                // 新增資料庫內容
                $sql_query = "INSERT INTO objectDetectionModelSelect (merchantUid, modelName) value ('$uid', '$fileName')";
                $_ = MysqlUtility::MysqlQuery($sql_query);
            }
            return array("Success", 200, "Success");
        } else {
            return array("缺少必要資料", 403, "Fail");
        }
    }
}

// =================================================================== //
// 以下為分割模型使用

class segmentationModelWeight {

    // 支援的食物類型以及資料類別
    private static $supportFoodType = array("Donburi", "SoupRice", "Rice", "Countable", "SoupNoodle", "Noodle", "SideDish", "SolidSoup", "Soup");
    private static $supportDataType = array("ObjectDetection", "Segmentation");
    private static $storageRoot = "D:/Storage";

    // 上傳分割資料
    static public function uploadTrainData() {
        $body = json_decode(file_get_contents('php://input'), true);
        if(Utility::checkIsValidData(["merchantUid", "foodType", "image", "imageHeight", "imageWidth", "food", "notFood"], $body)) {
            $uid = $body["merchantUid"];
            $foodType = $body["foodType"];
            $imageBase64 = $body["image"];
            $imageHeight = $body["imageHeight"];
            $imageWidth = $body["imageWidth"];
            $food = $body["food"];
            $notFood = $body["notFood"];

            if(!in_array($foodType, self::$supportFoodType)) {
                return array("不支援該食物類別", 401, "Not support food type");
            }

            $fileName = uniqid();
            $annotationInfo = array(
                "version" => "5.0.2",
                "flags" => (object) array(),
                "shapes" => array(
                    array(
                        "label" => "Food",
                        "points" => $food,
                        "group_id" => null,
                        "shape_type" => "polygon",
                        "flags" => (object) array()
                    ),
                    array(
                        "label" => "NotFood",
                        "points" => $notFood,
                        "group_id" => null,
                        "shape_type" => "polygon",
                        "flags" => (object) array()
                    )
                ),
                "imagePath" => $fileName,
                "imageData" => null,
                "imageHeight" => $imageHeight,
                "imageWidth" => $imageWidth
            );

            $rootFolder = self::$storageRoot."/$uid"."/Segmentation";
            self::createFolderIfNotFound($rootFolder);
            $rootFolder = $rootFolder."/$foodType";
            self::createFolderIfNotFound($rootFolder);
            $imageFolder = $rootFolder."/imgs";
            self::createFolderIfNotFound($imageFolder);
            $annotationFolder = $rootFolder."/annotations";
            self::createFolderIfNotFound($annotationFolder);

            $annotationInfoJson = json_encode($annotationInfo, JSON_PRETTY_PRINT);
            $annotationFilePath = $annotationFolder."/$fileName".".json";
            file_put_contents($annotationFilePath, $annotationInfoJson);

            $image = Utility::base64ImageTransform($imageBase64);
            $imageFilePath = $imageFolder."/$fileName".".jpg";
            file_put_contents($imageFilePath, $image);
            return array($annotationInfo, 200, "Success");
        } else {
            return array("缺少必要資料", 403, "Fail");
        }
    }

    // 訓練分割模型
    static public function trainSegmentationModel() {
        $body = json_decode(file_get_contents('php://input'), true);
        if(Utility::checkIsValidData(["merchantUid"], $body)) {
            $uid = $body["merchantUid"];

            if(!Utility::checkIsMerchantExist($uid)) {
                return array("商家不存在", 404, "Fail");
            }

            $sql_query = "SELECT * FROM train WHERE merchantUid = '$uid'";
            $data = MysqlUtility::MysqlQuery($sql_query);
            if(mysqli_num_rows($data) > 0) {
                $trainInfo = mysqli_fetch_array($data, MYSQLI_ASSOC);
                $trainType = $trainInfo["trainType"];
                $startTime = $trainInfo["startTime"];
                $result = [
                    "status" => "Reject",
                    "trainType" => $trainType,
                    "startTime" => $startTime
                ];
                return array($result, 201, "Reject");
            } else {
                $sql_query = "INSERT INTO train (merchantUid, trainType) VALUES ('$uid', '第二階段')";
                MysqlUtility::MysqlQuery($sql_query);
            }
            // 先暫時在這裡結束
            return array("Success", 200, "Success");

            $commend = "python python/Segmentation/SegmentationTrain.py $uid $root";
            exec($commend, $out);
            return array($out, 200, "Success");
        } else {
            return array("缺少必要資料", 403, "Fail");
        }
    }

    // 若查無此資料夾就會新建一個
    static private function createFolderIfNotFound($filePath) {
        if(!file_exists($filePath)) {
            mkdir($filePath);
        }
    }
}