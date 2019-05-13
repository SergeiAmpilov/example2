<?php

namespace RcrmWrapperModule;

use RetailCrm\ApiClient;
use RcrmWrapperModule\Customer;

class Order
{
    /**
     * @var ApiClient $api
     */
    private $api;

    /**
     * @var array $params
     */
    private $params;
    private $number;
    private $externalId;
    private $createdAt;
    private $customer;
    private $paymentType;
    private $paymentStatus;
    private $paymentAmount;
    private $deliveryCode;
    private $deliveryAddressText;
    private $source;
    private $medium;
    private $campaign;
    private $keyword;
    private $items;
    private $discount;
    private $shippingCost;
    private $couponCodes;


    public function __construct($api, $order, $orderNumberPostfix)
    {
        try {

            $this->api = $api;
            $this->externalId = '100' . $order['id'] . $orderNumberPostfix;
            $this->number = '100' . $order['id'] . $orderNumberPostfix;
            $this->createdAt = $order['create_datetime'];
            $this->customer = new Customer($this->api, $order['contact']);
            $this->customer->processCustomer();
            $this->setPaymentType(empty($order['params']['payment_id']) ? "" : $order['params']['payment_id']);
            $this->paymentAmount = $order['total'];
            $this->setDeliveryCode($order['params']['shipping_id']);
            $this->setDeliveryAdressText($order['params']);
            $this->source = empty($order['params']['utm_source']) ? "" : $order['params']['utm_source'];
            $this->medium = empty($order['params']['utm_medium']) ? "" : $order['params']['utm_medium'];
            $this->campaign = empty($order['params']['utm_campaign']) ? "" : $order['params']['utm_campaign'];
            $this->keyword = empty($order['params']['utm_term']) ? "" : $order['params']['utm_term'];
            $this->discount = $order['discount'];
            $this->shippingCost = $order['shipping'];
            $this->setItems($order['items']);
            $this->couponCodes = empty($order["coupon"]["code"]) ? "" : $order["coupon"]["code"];

        } catch (\Exception $e) {
            throw new \Exception('Retail\Order::construct->' . $e->getMessage() . '->');
        }
    }

    public function setPaymentType($webasystPaymentId)
    {
        switch ($webasystPaymentId) {
            case '8':
                $this->paymentType = 'cash';
                $this->paymentStatus = 'not-paid';
                break;
            case '4':
                $this->paymentType = 'prepay';
                $this->paymentStatus = 'not-paid';
                break;
            case '6':
                $this->paymentType = 'bill';
                $this->paymentStatus = 'not-paid';
                break;
            case '7':
                $this->paymentType = 'prepay';
                $this->paymentStatus = 'not-paid';
                break;
            case '2':
                $this->paymentType = 'cash';
                $this->paymentStatus = 'not-paid';
                break;
            case '9':
                $this->paymentType = 'cash';
                $this->paymentStatus = 'not-paid';
                break;
            case '10':
                $this->paymentType = 'bill';
                $this->paymentStatus = 'not-paid';
                break;
            case '11':
                $this->paymentType = 'prepay';
                $this->paymentStatus = 'not-paid';
                break;
            case "":
                $this->paymentType = '';
                $this->paymentStatus = '';
                break;
        }
    }


    public function setDeliveryCode($webasystDeliveryId)
    {
        switch ($webasystDeliveryId) {
            case '9':
                $this->deliveryCode = 'shoplogistics';
                break;
            case '10':
                $this->deliveryCode = 'sdek';
                break;
            case '11':
                $this->deliveryCode = 'self-delivery';
                break;
            case '12':
                $this->deliveryCode = 'mig';
                break;
            case '5':
                $this->deliveryCode = 'russia-post';
                break;
            case '6':
                $this->deliveryCode = 'novaposhta';
                break;
            case '7':
                $this->deliveryCode = 'self-delivery';
                break;
            case '8':
                $this->deliveryCode = 'courier';
                break;

        }
    }

    public function setDeliveryAdressText($params)
    {
        $shCity = empty($params['shipping_address.city']) ? "" : $params['shipping_address.city'];
        $shStreet = empty($params['shipping_address.street']) ? "" : $params['shipping_address.street'];
        $shZip = empty($params['shipping_address.zip']) ? "" : $params['shipping_address.zip'];
        
        $this->deliveryAddressText = $shCity . ', ' . $shStreet . ' ' . $shZip;


    }

    public function setItems($items)
    {
         if (!empty($items)) {
             foreach ($items as $item) {

                 $this->items[] = [
                     'offer' => [
                         'xmlId' => $item['sku_code']
                     ],
                     'quantity' => $item['quantity']
                 ];

             }
         }
    }

    public function processOrder()
    {
        $this->params = [
            'externalId' => $this->externalId,
            'number' => $this->number,
            'createdAt' => $this->createdAt,
            'customer' => [
                'id' => $this->customer->getRetailId()
            ],
            'delivery' => [
                'code' => $this->deliveryCode,
                'address' => [
                    'text' => $this->deliveryAddressText
                ],
                'cost' => $this->shippingCost
            ],
            'source' => [
                'source' => $this->source,
                'medium' => $this->medium,
                'campaign' => $this->campaign,
                'keyword' => $this->keyword
            ],
            'items' => $this->items,
            'discountManualAmount' => $this->discount,
            'orderMethod' => 'shopping-cart',
            'firstName' => $this->customer->getFirstName(),
            'lastName' => $this->customer->getLastName(),
            'email' => $this->customer->getEmail(),
            'phone' => $this->customer->getPhone(),
            'customFields' => [
                'coupon_code' => $this->couponCodes
            ]

        ];
    }

    public function getExternalId()
    {
        return $this->externalId;
    }

    public function isExist()
    {
        $response = $this->api->request->ordersList([
            'externalIds' => [$this->externalId]
        ]);

        $isRequestSuccessful = $response->isSuccessful() && 200 === $response->getStatusCode();

        if (!$isRequestSuccessful) {
            throw new \Exception('Retail\Order::isExist->' . $response->getStatusCode() . '->');

        }

        if (!empty($response['orders'])) {
            return true;
        }

        return false;
    }

    public function createOrder()
    {

        if (!$this->isExist()) {
            $response = $this->api->request->ordersCreate($this->params);
            $isRequestSuccessful = $response->isSuccessful() && 201 === $response->getStatusCode();

            if (!$isRequestSuccessful) {
                throw new \Exception('Retail\Order::createOrder->' . $response->getStatusCode() . '->');

            }
        }

    }

    public function createPayment()
    {
        $params = [
            'type' => $this->paymentType,
            'status' => $this->paymentStatus,
            'paidAt' => $this->createdAt,
            'amount' => $this->paymentAmount,
            'order' => [
                'externalId' => $this->externalId
            ]
        ];
        $response = $this->api->request->ordersPaymentCreate($params);

        $isRequestSuccessful = $response->isSuccessful() && 201 === $response->getStatusCode();

        if (!$isRequestSuccessful) {
            throw new \Exception('Retail\Order::createPayment->' . $response->errorMsg . '->');

        }
    }
    
}