<?php

require 'vendor/autoload.php';

use SiteApiModule\WebasystApi;
use SiteApiModule\Exception\CurlExecException;
use SiteApiModule\Exception\InvalidTokenException;
use ProcessingModule\OrdersHandler;
use RcrmWrapperModule\Order;

date_default_timezone_set('Europe/Moscow');
$time = file_get_contents('configs/timeDexshell.txt');
$timeNow = time();
file_put_contents('configs/timeDexshell.txt', $timeNow - 1);
$congig = include('configs/config.php');
$webApi = new WebasystApi($congig['tokenDexshell'], 'dexshell.ru');
$client = new \RetailCrm\ApiClient(
    'https://fonarik-market2.retailcrm.ru',
    'U6Vsd5c1nJHaka60Gc4E92TbwoSdBU4m',
    \RetailCrm\ApiClient::V5
);


try {
    $result = $webApi->shopOrderSearch();

    $resultOrders = OrdersHandler::timeFiltration($result->orders, $time);

} catch (CurlExecException $e) {
    $message = date('Y-m-d H:i:s', time()) . $e->getMessage() . PHP_EOL;
    file_put_contents('logs/webasyst_api_dexshell.log', print_r($message, 1), FILE_APPEND);
    exit(1);
} catch (InvalidTokenException $e) {
    $message = date('Y-m-d H:i:s', time()) . $e->getMessage() . PHP_EOL;
    file_put_contents('logs/webasyst_api_dexshell.log', print_r($message, 1), FILE_APPEND);
    exit(1);
}

foreach ($resultOrders as $order) {
    try {

        $waOrder = $webApi->shopOrderGetInfo($order->id);
        $order = new Order($client, $waOrder, 'DEX');
        $order->processOrder();
        $order->createOrder();
        $order->createPayment();
        $order = null;

    } catch
    (CurlExecException $e) {
        $message = date('Y-m-d H:i:s', time()) . $e->getMessage() . PHP_EOL;
        file_put_contents('logs/webasyst_api_dexshell.log', print_r($message, 1), FILE_APPEND);
        continue;
    } catch (InvalidTokenException $e) {
        $message = date('Y-m-d H:i:s', time()) . $e->getMessage() . PHP_EOL;
        file_put_contents('logs/webasyst_api_dexshell.log', print_r($message, 1), FILE_APPEND);
        continue;
    } catch (\Exception $e) {
        $message = date('Y-m-d H:i:s', time()) . $e->getMessage() . 'order_id: ' . $order['id'] . PHP_EOL;
        file_put_contents('logs/processed_dexshell.log', print_r($message, 1), FILE_APPEND);
        continue;
    }
}

