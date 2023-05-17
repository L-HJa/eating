<?php
$page = $_REQUEST['page'];
$mode = $_REQUEST['mode'];
$user = $_REQUEST['user'];

// echo $mode;
if($user == 'merchant'){
    if($page == 'user'){
        if($mode == 'signin'){
            require('userSignin.php');
            $data = signinRequest::getRequest();    
        }elseif($mode == 'login'){
            require('userLogin.php');
            $data = loginRequest::getRequest();    
        }elseif($mode == 'photo'){
            require('photoRequest.php');
            $data = photoRequest::getRequest();
        }else{
            require('userRequest.php');
            $data = userRequest::getRequest();
        }
    }elseif($page == 'table'){
        require('tableRequest.php');
        if($mode != NULL){
            $Func = $mode;
            $data = mercnantGetInfo::$Func();
        }else{
            $data = tableRequest::getRequest();
        }
        /*if($mode == 'all_table_info') {
            // 獲取店家所有桌子資訊，不包含桌子內的食物資訊
            $data = tableRequest::getAllTableInfo();
        } elseif($mode == 'single_table_info') {
            // 獲取單張桌子的資訊
            $data = tableRequest::getSingleTableInfo();
        } elseif($mode == 'all_table_with_food_info') {
            // 獲取所有桌子以及當中的食物資訊
            $data = tableRequest::getAllTableWithFoodInfo();
        } elseif($mode == 'check_table_is_exist') {
            $data = tableRequest::checkTableIsExist();
        } else {
            $data = tableRequest::getRequest();
        }*/
    }elseif($page == 'food'){
        require('foodRequest.php');
        $data = foodRequest::getRequest();
    }elseif($page == 'item'){
        require('itemRequest.php');
        if($mode == 'all-item-of-merchant'){
            $data = itemRequest::getAllItem();
        }else{
            $data = itemRequest::getRequest();
        }
    }elseif($page == 'model_weight'){
        require('modelWeight.php');
        if($mode == 'upload-object-detection-single-image') {
            $data = objectDetectionModelWeight::uploadObjectDetectionSingleImage();
        } elseif($mode == 'train-object-detection-model') {
            $data = objectDetectionModelWeight::trainObjectDetectionModel();
        } elseif($mode == 'train-image-count') {
            $data = objectDetectionModelWeight::trainImageCount();
        } elseif($mode == 'object-detection-train-image-count') {
            $data = objectDetectionModelWeight::objectDetectionTrainImageCount();
        } elseif($mode == 'object-detection-train-image-info') {
            $data = objectDetectionModelWeight::objectDetectionTrainImageInfo();
        } elseif($mode == 'delete-object-detection-train-image') {
            $data = objectDetectionModelWeight::deleteObjectDetectionTrainImage();
        } elseif($mode == 'save-python-pid') {
            $data = objectDetectionModelWeight::savePythonPid();
        } elseif($mode == 'delete-train-status') {
            $data = objectDetectionModelWeight::deleteTrainStatus();
        } elseif($mode == 'stop-train') {
            $data = objectDetectionModelWeight::stopTrain();
        } elseif($mode == 'check-is-train') {
            $data = objectDetectionModelWeight::checkIsTrain();
        } elseif($mode == 'fetch-object-detection-model-info') {
            $data = objectDetectionModelWeight::fetchObjectDetectionModelInfo();
        } elseif($mode == 'change-object-detection-weight-name') {
            $data = objectDetectionModelWeight::changeObjectDetectionWeightName();
        } elseif($mode == 'object-detection-model-selected') {
            $data = objectDetectionModelWeight::objectDetectionModelSelected();
        } elseif($mode == 'upload-segmentation-single-image') {
            $data = segmentationModelWeight::uploadTrainData();
        } elseif($mode == 'train-segmentation-model') {
            $data = segmentationModelWeight::trainSegmentationModel();
        }
    }else{
        require('Request.php');
        $data = Request::getRequest();
    }
}elseif($user == 'customer'){
    if($page == 'user'){
        if($mode == 'signin'){
            require('userSignin.php');
            $data = signinRequest::getRequest_cus();    
        }elseif($mode == 'login'){
            require('userLogin.php');
            $data = loginRequest::getRequest();    
        }elseif($mode == 'photo'){
            require('photoRequest.php');
            $data = photoRequest::getRequest();
        }else{
            require('userRequest.php');
            if($mode == 'favorite'){
                $data = customerFavorite::getRequest();
            }elseif($mode != NULL){
                $Func = $mode;
                $data = customerGetMerchantInfo::$Func();
            }else{
                $data = customerRequest::getRequest();
            }
        }
    }elseif($page == 'food'){
        require('foodRequest.php');
        $data = customerGetFoodInfo::getRequest();
    }
}

require('Response.php');
Response::sendResponse($data[0], $data[1], $data[2]);
?>