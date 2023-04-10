<?php
$page = $_REQUEST['page'];
$mode = $_REQUEST['mode'];
// echo $mode;
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
    $data = tableRequest::getRequest();
}elseif($page == 'food'){
    require('foodRequest.php');
    $data = foodRequest::getRequest();
}else{
    require('Request.php');
    $data = Request::getRequest();
}
require('Response.php');
Response::sendResponse($data[0], $data[1], $data[2]);
?>