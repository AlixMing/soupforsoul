<?php


    function sendMsg($phone) {
        $url = "http://wap.greedc.com:8005/msg/sendMessage?";
        $key = '123456789';
        $msg = '尊敬的客户，感谢您申请“薪e贷”产品，本平台已收到您的借款需求，请耐心等待，客服将在1个工作日内与您联系！请保持电话畅通！如有疑问请拨打客服热线：0756-8336111';
        $token = sha1($phone.$msg.'1'.$key);
        $params = "token=".$token."&phone=".$phone."&msg=".$msg."&type=1";

        return https_request($url, $params);
    }
    
    function sendMsg1($phone, $msg) {
        $url = "http://wap.greedc.com:8005/msg/sendMessage?";
        $key = '123456789';
        $token = sha1($phone.$msg.'1'.$key);
        $params = "token=".$token."&phone=".$phone."&msg=".$msg."&type=1";

        return https_request($url, $params);
    }

?>
