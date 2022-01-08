<?php

namespace App\Controller;

use App\Traits\Databases;

class OrderController
{
    use Databases;

    public static function order() : void {

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
    }
}