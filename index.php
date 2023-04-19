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
        if($mode == 'all_table_info') {
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
        }
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
            $data = modelWeight::uploadObjectDetectionSingleImage();
        } elseif($mode == 'train-object-detection-model') {
            $data = modelWeight::trainObjectDetectionModel();
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
            $data = loginRequest::getRequest_cus();    
        }elseif($mode == 'photo'){
            require('photoRequest.php');
            $data = photoRequest::getRequest();
        }else{
            require('userRequest.php');
            $data = userRequest::getRequest_cus();
        }
    }
}

require('Response.php');
Response::sendResponse($data[0], $data[1], $data[2]);
?>