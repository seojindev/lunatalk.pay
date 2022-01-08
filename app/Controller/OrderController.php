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
            $view = file_get_contents(VIEWS . '/' . 'index.html');

            $view = str_replace('^order_uuid^', $order['result']['uuid'], $view);

            echo $view;
        }
    }
}