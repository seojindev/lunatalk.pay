<?php

namespace App\Traits;

use MysqliDb;

trait Databases
{
    private $instance;

    public static $DB;

    public function __construct()
    {
        echo "__construct";
    }

    public static function init()
    {
        self::$DB = new MysqliDb ([
            'host' => $_ENV["DB_HOST"],
            'username' => $_ENV["DB_USERNAME"],
            'password' => $_ENV["DB_PASSWORD"],
            'db'=> $_ENV["DB_DATABASE"],
            'port' => $_ENV["DB_PORT"]
        ]);
    }

    public static function getOrderData($uuid = '') : array {

        $db = self::$DB;

        $db->where ('uuid', $uuid);
        $master = $db->getOne ('order_masters');

        if(!$master) {
            return [
                'state' => false,
                'error' => $db->getLastError()
            ];
        }

        $db->where ('order_id', $master['id']);
        $products = $db->get('order_products');

        return [
            'state' => true,
            'result' => [
                'id' => $master['id'],
                'uuid' => $master['uuid'],
                'order_name' => $master['order_name'],
                'order_price' => $master['order_price'],
            ]
        ];
    }

    public static function insertPayments($params = []) {
        $db = self::$DB;
        $order = self::getOrderData($params['orderId']);

        $task = $db->insert ('order_payments', [
            'order_id' => $order['result']['id'],
            'mId' => isset($params['mId']) && $params['mId'] ? $params['mId'] : '',
            'version' => isset($params['version']) && $params['version'] ? $params['version'] : '',
            'transactionKey' => isset($params['transactionKey']) && $params['transactionKey'] ? $params['transactionKey'] : '',
            'paymentKey' => isset($params['paymentKey']) && $params['paymentKey'] ? $params['paymentKey'] : '',
            'orderId' => isset($params['orderId']) && $params['orderId'] ? $params['orderId'] : '',
            'orderName' => isset($params['orderName']) && $params['orderName'] ? $params['orderName'] : '',
            'method' => isset($params['method']) && $params['method'] ? $params['method'] : '',
            'status' => isset($params['status']) && $params['status'] ? $params['status'] : '',
            'requestedAt' => isset($params['requestedAt']) && $params['requestedAt'] ? $params['requestedAt'] : '',
            'approvedAt' => isset($params['approvedAt']) && $params['approvedAt'] ? $params['approvedAt'] : '',
            'useEscrow' => isset($params['useEscrow']) && $params['useEscrow'] ? $params['useEscrow'] : '',
            'cultureExpense' => isset($params['cultureExpense']) && $params['cultureExpense'] ? $params['cultureExpense'] : '',
            'transfer' => isset($params['transfer']) && $params['transfer'] ? $params['transfer'] : '',
            'mobilePhone' => isset($params['mobilePhone']) && $params['mobilePhone'] ? $params['mobilePhone'] : '',
            'giftCertificate' => isset($params['giftCertificate']) && $params['giftCertificate'] ? $params['giftCertificate'] : '',
            'cashReceipt' => isset($params['cashReceipt']) && $params['cashReceipt'] ? $params['cashReceipt'] : '',
            'discount' => isset($params['discount']) && $params['discount'] ? $params['discount'] : '',
            'cancels' => isset($params['cancels']) && $params['cancels'] ? $params['cancels'] : '',
            'secret' => isset($params['secret']) && $params['secret'] ? $params['secret'] : '',
            'type' => isset($params['type']) && $params['type'] ? $params['type'] : '',
            'easyPay' => isset($params['easyPay']) && $params['easyPay'] ? $params['easyPay'] : '',
            'currency' => isset($params['currency']) && $params['currency'] ? $params['currency'] : '',
            'totalAmount' => isset($params['totalAmount']) && $params['totalAmount'] ? $params['totalAmount'] : 0,
            'balanceAmount' => isset($params['balanceAmount']) && $params['balanceAmount'] ? $params['balanceAmount'] : 0,
            'suppliedAmount' => isset($params['suppliedAmount']) && $params['suppliedAmount'] ? $params['suppliedAmount'] : 0,
            'vat' => isset($params['vat']) && $params['vat'] ? $params['vat'] : 0,
            'taxFreeAmount' => isset($params['taxFreeAmount']) && $params['taxFreeAmount'] ? $params['taxFreeAmount'] : 0,
            'created_at' => $db->now(),
            'updated_at' => $db->now()
        ]);

        if(isset($params['method']) && $params['method'] == '카드') {
            $cardArray = (array) $params['card'];

            $db->insert ('order_payments_cards', [
                'pay_id' => $task,
                'company' => isset($cardArray['company']) && $cardArray['company'] ? $cardArray['company'] : '',
                'number' => isset($cardArray['number']) && $cardArray['number'] ? $cardArray['number'] : '',
                'installmentPlanMonths' => isset($cardArray['installmentPlanMonths']) && $cardArray['installmentPlanMonths'] ? $cardArray['installmentPlanMonths'] : '',
                'isInterestFree' => isset($cardArray['isInterestFree']) && $cardArray['isInterestFree'] ? $cardArray['isInterestFree'] : '',
                'approveNo' => isset($cardArray['approveNo']) && $cardArray['approveNo'] ? $cardArray['approveNo'] : '',
                'useCardPoint' => isset($cardArray['useCardPoint']) && $cardArray['useCardPoint'] ? $cardArray['useCardPoint'] : '',
                'cardType' => isset($cardArray['cardType']) && $cardArray['cardType'] ? $cardArray['cardType'] : '',
                'ownerType' => isset($cardArray['ownerType']) && $cardArray['ownerType'] ? $cardArray['ownerType'] : '',
                'acquireStatus' => isset($cardArray['acquireStatus']) && $cardArray['acquireStatus'] ? $cardArray['acquireStatus'] : '',
                'receiptUrl' => isset($cardArray['receiptUrl']) && $cardArray['receiptUrl'] ? $cardArray['receiptUrl'] : '',
                'created_at' => $db->now(),
                'updated_at' => $db->now(),

            ]);
        } else if(isset($params['method']) && $params['method'] == '가상계좌') {
            $virtualArray = (array) $params['virtualAccount'];

            $db->insert ('order_payments_virtuals', [
                'pay_id' => $task,
                'accountNumber' => isset($virtualArray['accountNumber']) && $virtualArray['accountNumber'] ? $virtualArray['accountNumber'] : '',
                'accountType' => isset($virtualArray['accountType']) && $virtualArray['accountType'] ? $virtualArray['accountType'] : '',
                'bank' => isset($virtualArray['bank']) && $virtualArray['bank'] ? $virtualArray['bank'] : '',
                'customerName' => isset($virtualArray['customerName']) && $virtualArray['customerName'] ? $virtualArray['customerName'] : '',
                'dueDate' => isset($virtualArray['dueDate']) && $virtualArray['dueDate'] ? $virtualArray['dueDate'] : '',
                'expired' => isset($virtualArray['expired']) && $virtualArray['expired'] ? $virtualArray['expired'] : '',
                'settlementStatus' => isset($virtualArray['settlementStatus']) && $virtualArray['settlementStatus'] ? $virtualArray['settlementStatus'] : '',
                'refundStatus' => isset($virtualArray['refundStatus']) && $virtualArray['refundStatus'] ? $virtualArray['refundStatus'] : '',
                'created_at' => $db->now(),
                'updated_at' => $db->now(),
            ]);
        }
    }

    public static function updateSuccessVirtualCallback($secret = '') {
        $db = self::$DB;

        $db->where ('secret', $secret);

        $db->update ('order_payments', [
            'status' => 'DONE',
            'approvedAt' => date('Y-m-d\TH:i:sP'),
        ]);
    }


    public static function insertNicapageMediaFiles($params = [])
    {
        $db = self::$DB;

        $task = $db->insert ('media_files', [
            'dest_path' => isset($params['dest_path']) && $params['dest_path'] ? $params['dest_path'] : NULL,
            'file_name' => isset($params['file_name']) && $params['file_name'] ? $params['file_name'] : NULL,
            'original_name' => isset($params['original_name']) && $params['original_name'] ? $params['original_name'] : NULL,
            'file_type' => isset($params['file_type']) && $params['file_type'] ? $params['file_type'] : NULL,
            'file_size' => isset($params['file_size']) && $params['file_size'] ? $params['file_size'] : NULL,
            'file_extension' => isset($params['file_extension']) && $params['file_extension'] ? $params['file_extension'] : NULL,
            'active' => 'Y',
            'created_at' => $db->now(),
            'updated_at' => $db->now()
        ]);

        if(!$task) {

            return [
                'state' => false,
                'error' => $db->getLastError()
            ];
        }

        return [
            'state' => true
        ];
    }
}