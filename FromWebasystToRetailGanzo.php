<?php

require 'vendor/autoload.php';

use SiteApiModule\WebasystApi;
use SiteApiModule\Exception\CurlExecException;
use SiteApiModule\Exception\InvalidTokenException;
use ProcessingModule\OrdersHandler;
use RcrmWrapperModule\Order;

date_default_timezone_set('Europe/Moscow');
$time = file_get_contents('configs/timeGanzo.txt');
$timeNow = time();
file_put_contents('configs/timeGanzo.txt', $timeNow - 1);
$congig = include('configs/config.php');
$webApi = new WebasystApi($congig['tokenGanzo'], 'ganzo.ua');
$client = new \RetailCrm\ApiClient(
    'https://fonarik2.retailcrm.ru',
    '2BGeBZ17lC49pxTBhxIVYp3qqaGHenBm',
    \RetailCrm\ApiClient::V5
);

try {
    $result = $webApi->shopOrderSearch();

    $resultOrders = OrdersHandler::timeFiltration($result->orders, $time);

} catch (CurlExecException $e) {
    $message = date('Y-m-d H:i:s', time()) . $e->getMessage() . PHP_EOL;
    file_put_contents('logs/webasyst_api_ganzo.log', print_r($message, 1), FILE_APPEND);
    exit(1);
} catch (InvalidTokenException $e) {
    $message = date('Y-m-d H:i:s', time()) . $e->getMessage() . PHP_EOL;
    file_put_contents('logs/webasyst_api_ganzo.log', print_r($message, 1), FILE_APPEND);
    exit(1);
}

foreach ($resultOrders as $order) {
    try {

        $waOrder = $webApi->shopOrderGetInfo($order->id);
        $order = new Order($client, $waOrder, 'GAN');
        $order->processOrder();
        $order->createOrder();
        $order->createPayment();
        $order = null;

    } catch
    (CurlExecException $e) {
        $message = date('Y-m-d H:i:s', time()) . $e->getMessage() . PHP_EOL;
        file_put_contents('logs/webasyst_api_ganzo.log', print_r($message, 1), FILE_APPEND);
        continue;
    } catch (InvalidTokenException $e) {
        $message = date('Y-m-d H:i:s', time()) . $e->getMessage() . PHP_EOL;
        file_put_contents('logs/webasyst_api_ganzo.log', print_r($message, 1), FILE_APPEND);
        continue;
    } catch (\Exception $e) {
        $message = date('Y-m-d H:i:s', time()) . $e->getMessage() . 'order_id: ' . $order['id'] . PHP_EOL;
        file_put_contents('logs/processed_ganzo.log', print_r($message, 1), FILE_APPEND);
        continue;
    }
}

