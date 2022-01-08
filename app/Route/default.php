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
        $view = file_get_contents(VIEWS . '/' . 'order.html');

        $view = str_replace('^order_client_key^', $_ENV['TOSS_CLIENT_KEY'], $view);
        $view = str_replace('^order_uuid^', $order['result']['uuid'], $view);
        $view = str_replace('^order_name^', $order['result']['order_name'], $view);
        $view = str_replace('^order_price^', $order['result']['order_price'], $view);
        $view = str_replace('^order_price2^', number_format($order['result']['order_price']), $view);

        echo $view;
    }
}, 'get');

Route::add('/v1/success', function() {
    Databases::init();

    $paymentKey = $_GET['paymentKey'];
    $orderId = $_GET['orderId'];
    $amount = $_GET['amount'];

    $secretKey = $_ENV['TOSS_SECRET_KEY'];

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

    if ($isSuccess) {
        $view = file_get_contents(VIEWS . '/' . 'success.html');

        $view = str_replace('^front_url^', $_ENV['FRONT_URL'], $view);

        echo $view;

    } else {
//        echo "<h1>결제 실패</h1>";
//        echo "<p>" .$responseJson->message . "</p>";
//        echo "<span>에러코드:" . $responseJson->code . "</span>";

        $view = file_get_contents(VIEWS . '/' . 'fail.html');

        $view = str_replace('^front_url^', $_ENV['FRONT_URL'], $view);
        echo $view;
    }

}, 'get');

Route::add('/v1/fail', function() {
    Databases::init();


    $view = file_get_contents(VIEWS . '/' . 'fail.html');

    $view = str_replace('^front_url^', $_ENV['FRONT_URL'], $view);

    echo $view;

}, 'get');

Route::run(BASEPATH);