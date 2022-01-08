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

        $view = str_replace('^order_uuid^', $order['result']['uuid'], $view);
        $view = str_replace('^order_name^', $order['result']['order_name'], $view);
        $view = str_replace('^order_price^', $order['result']['order_price'], $view);
        $view = str_replace('^order_price2^', number_format($order['result']['order_price']), $view);

        echo $view;
    }
}, 'get');

Route::add('/v1/success', function() {
    Databases::init();

    echo "success";
}, 'get');

Route::add('/v1/fail', function() {
    Databases::init();

    echo "fail";
}, 'get');

Route::run(BASEPATH);