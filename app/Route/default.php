<?php

use App\Traits\Databases;
use App\Util\Route;

Route::add('/test', function() {
    echo BASEPATH;
}, 'get');


Route::add('/v1/order', function() {

    Databases::init();

    $order = Databases::getOrderData($_GET['uuid']);

    if($order['state'] === false) {
        echo "error";
    } else {

        if($order['result']['active'] === 'Y') {

            $view = file_get_contents(VIEWS . '/' . 'fail_order.html');
            $view = str_replace('^front_url^', $_ENV['FRONT_URL'], $view);
            echo $view;
            exit;
        }

        $view = file_get_contents(VIEWS . '/' . 'order.html');

        $view = str_replace('^order_client_key^', $_ENV['TOSS_CLIENT_KEY'], $view);
        $view = str_replace('^order_uuid^', $order['result']['uuid'], $view);
        $view = str_replace('^order_name^', $order['result']['order_name'], $view);
        $view = str_replace('^order_user_name^', $order['result']['order_user_name'], $view);
        //$view = str_replace('^order_price^', $order['result']['order_price'], $view);
        //$view = str_replace('^order_price2^', number_format($order['result']['order_price']), $view);
        $view = str_replace('^order_price^', 200, $view);
        $view = str_replace('^order_price2^', 200, $view);

        echo $view;
    }
}, 'get');

Route::add('/v1/success', function() {
    Databases::init();

    $paymentKey = $_GET['paymentKey'];
    $orderId = $_GET['orderId'];
    $amount = $_GET['amount'];

    $secretKey = $_ENV['TOSS_SECRET_KEY'];
    $order = Databases::getOrderData($orderId);

    if($order['result']['active'] === 'Y') {

        $view = file_get_contents(VIEWS . '/' . 'fail_order.html');
        $view = str_replace('^front_url^', $_ENV['FRONT_URL'], $view);
        echo $view;
        exit;
    }

    $url = 'https://api.tosspayments.com/v1/payments/' . $paymentKey;

    $data = ['orderId' => $orderId, 'amount' => $amount];

    $credential = base64_encode($secretKey . ':');

    $curlHandle = curl_init($url);

    curl_setopt_array($curlHandle, [
        CURLOPT_POST => TRUE,
        CURLOPT_RETURNTRANSFER => TRUE,
        CURLOPT_HTTPHEADER => [
            'Authorization: Basic ' . $credential,
            'Content-Type: application/json'
        ],
        CURLOPT_POSTFIELDS => json_encode($data)
    ]);

    $response = curl_exec($curlHandle);

    $httpCode = curl_getinfo($curlHandle, CURLINFO_HTTP_CODE);
    $isSuccess = $httpCode == 200;
    $responseJson = json_decode($response);
    $responseArray = (array) json_decode($response);

    if ($isSuccess) {

        Databases::insertPayments($responseArray);

        if($responseArray['method'] == '가상계좌') {

            Databases::updateActive($orderId, '5100020'); // 결제 완료 처리.

            $orderLogMessage = date('YmdHis').": 성공(가상계좌)";
            Databases::updateOrderLog($orderId, $orderLogMessage);

            $view = file_get_contents(VIEWS . '/' . 'success_virtual.html');
        } else {

            Databases::updateActive($orderId, '5100030'); // 결제 대기 처리.

            $orderLogMessage = date('YmdHis').": 결제 성공(카드)";
            Databases::updateOrderLog($orderId, $orderLogMessage);

            $view = file_get_contents(VIEWS . '/' . 'success.html');
        }


        $view = str_replace('^front_url^', $_ENV['FRONT_URL'], $view);

        echo $view;

    } else {

        $orderLogMessage = date('YmdHis').": 결제 실패";
        $orderLogMessage .= $responseJson->message;
        $orderLogMessage .= $responseJson->code;
        Databases::updateOrderLog($orderId, $orderLogMessage);

        Databases::updateActive($orderId, '5100000'); // 결제 실패 처리.

        $view = file_get_contents(VIEWS . '/' . 'fail.html');

        $view = str_replace('^error_message^', $responseJson->message, $view);
        $view = str_replace('^error_code^', $responseJson->code, $view);

        $view = str_replace('^front_url^', $_ENV['FRONT_URL'], $view);
        echo $view;
    }

    $path = "./logs/" . date('Ymd');
    if (!file_exists($path)) {
        mkdir($path, 0777, true);
    }

    ob_start();
    print_r($responseJson);
    $result = ob_get_clean();
    file_put_contents($path . '/' . 'success_get_'.date('YmdHis').'.txt', $result);

}, 'get');

Route::add('/v1/fail', function() {
    Databases::init();

    $view = file_get_contents(VIEWS . '/' . 'fail.html');

    $view = str_replace('^front_url^', $_ENV['FRONT_URL'], $view);

    echo $view;

}, 'get');


Route::add('/v1/vcallback', function() {

    Databases::init();

    $postData = file_get_contents('php://input');
    $json = json_decode($postData);

    if ($json->status == 'DONE') {
        Databases::updateSuccessVirtualCallback($json->secret);
        Databases::updateSuccessVirtualCallbackPayState($json->orderId);
    }

    $path = "./logs/" . date('Ymd');
    if (!file_exists($path)) {
        mkdir($path, 0777, true);
    }

    ob_start();
    print_r($json);
    $result = ob_get_clean();
    file_put_contents($path . '/' . 'vcallback_post_'.date('YmdHis').'.txt', $result);
}, 'post');


Route::add('/v1/webhook', function() {
    $postData = file_get_contents('php://input');
    $json = json_decode($postData);

    if ($json->status == 'DONE') {
        // handle deposit result
    }

    $path = "./logs/" . date('Ymd');
    if (!file_exists($path)) {
        mkdir($path, 0777, true);
    }

    ob_start();
    print_r($json);
    $result = ob_get_clean();
    file_put_contents($path . '/' . 'webhook_post_'.date('YmdHis').'.txt', $result);

}, 'post');

Route::run(BASEPATH);