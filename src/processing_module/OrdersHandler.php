<?php

namespace ProcessingModule;

class OrdersHandler
{
    /**
     * Фильтрует массив заказов по времени
     * @static
     * @param array $arrayToProcess массив, который нужно отфильтровать
     * @param string $time время, по которому нужно отфильтровать
     * @return array отфильтрованнный массив
     */
    public static function timeFiltration($arrayToProcess, $time)
    {
        $resultArray = [];
        foreach ($arrayToProcess as $order) {
            $property = 'update_datetime';
            if ($order->update_datetime == '') {
                $property = 'create_datetime';
            }
            if (strtotime($order->$property) > (int)$time) {
                $tmpOrder = new \stdClass();
                $tmpOrder->id = $order->id;
                $resultArray[] = $tmpOrder;
                $tmpOrder = null;
            }
        }
        return $resultArray;
    }

    /**
     * @param string $state
     * @return array
     */
    public static function getState($state)
    {
        //Передано в комплектацию 5c4dfde7-8199-11e6-7a69-93a7001d6dd7
        //Комплектуется e77d81b3-aa45-11e6-7a31-d0fd0071cb55
        //Укомплектован df6cb23b-7366-11e6-7a69-8f55001d585b
        //Отгружен df6cb435-7366-11e6-7a69-8f55001d585c
        //Доставлен df6cb54c-7366-11e6-7a69-8f55001d585d
        //Возврат df6cb72a-7366-11e6-7a69-8f55001d585e
        //Отменен df6cb889-7366-11e6-7a69-8f55001d585f
        $stateId = '';
        switch ($state) {
            case 'new':
                $stateId = 'df6cae16-7366-11e6-7a69-8f55001d5859';
                break;
            case 'paid':
                $stateId = 'df6cb0d0-7366-11e6-7a69-8f55001d585a';
                break;
            case 'processing':
                $stateId = '12d2d340-9e78-11e8-9ff4-34e80008b981';
                break;
            case 'completed':
                $stateId = 'f45a8d2b-aa43-11e6-7a69-8f55005526df';
                break;
            case 'deleted':
                $stateId = 'df6cb889-7366-11e6-7a69-8f55001d585f';
                break;
        }
        $state = [
            'meta' => [
                'href' => 'https://online.moysklad.ru/api/remap/1.2/entity/customerorder/metadata/states/' . $stateId,
                'type' => 'state',
                'mediaType' => 'application/json'
            ]
        ];
        return $state;
    }

    /**
     * @param string $customerId
     * @return array
     */
    public static function getCustomer($customerId)
    {
        $customer = [
            'meta' => [
                'href' => 'https://online.moysklad.ru/api/remap/1.2/entity/counterparty/' . $customerId,
                'type' => 'counterparty',
                'mediaType' => 'application/json'
            ]
        ];
        return $customer;
    }

    /**
     * @param array $productsIds
     * @return array
     */
    public static function getPositions($productsIds)
    {
        $positions = [];
        foreach ($productsIds as $id => $product) {
            if ($id == '1d8de013-a20d-11e8-9ff4-34e800055809') {
                $positions[] = [
                    'quantity' => (float)$product['quantity'],
                    'price' => $product['price'] * 100,
                    'assortment' => [
                        'meta' => [
                            'href' => 'https://online.moysklad.ru/api/remap/1.2/entity/service/' . $id,
                            'type' => 'service',
                            'mediaType' => 'application/json'
                        ]
                    ],
                    'reserve' => (float)$product['quantity']
                ];
            } else {
                $positions[] = [
                    'quantity' => (float)$product['quantity'],
                    'price' => $product['price'] * 100,
                    'assortment' => [
                        'meta' => [
                            'href' => 'https://online.moysklad.ru/api/remap/1.2/entity/product/' . $id,
                            'type' => 'product',
                            'mediaType' => 'application/json'
                        ]
                    ],
                    'reserve' => (float)$product['quantity']
                ];
            }
        }
        return $positions;
    }

    public static function getComment($order)
    {
        $result = '';
        if (!empty($order['comment'])) {
            $result .= 'Комментарий: ' . $order['comment'] . PHP_EOL;
        }

        return $result;
    }

    public static function getAttributes($order)
    {
        $result = [];
        if (isset($order['params']['shipping_address.city']) && !empty($order['params']['shipping_address.city'])) {
            $result[] = [
                'id' => '709e197e-a209-11e8-9109-f8fc00057372',
                'value' => $order['params']['shipping_address.city']
            ];
        }
        if (isset($order['params']['shipping_address.street']) && !empty($order['params']['shipping_address.street'])) {
            $result[] = [
                'id' => '709e1e0d-a209-11e8-9109-f8fc00057373',
                'value' => $order['params']['shipping_address.street']
            ];
        }
        if (isset($order['params']['shipping_address.zip']) && !empty($order['params']['shipping_address.zip'])) {
            $result[] = [
                'id' => '709e2096-a209-11e8-9109-f8fc00057374',
                'value' => $order['params']['shipping_address.zip']
            ];
        }
        if (isset($order['params']['shipping_est_delivery']) && !empty($order['params']['shipping_est_delivery'])) {
            $result[] = [
                'id' => 'b812b90e-a20e-11e8-9ff4-315000056c6e',
                'value' => $order['params']['shipping_est_delivery']
            ];
        }
        if (isset($order['params']['shipping_name']) && !empty($order['params']['shipping_name'])) {
            $result[] = [
                'id' => '709e2476-a209-11e8-9109-f8fc00057376',
                'value' => $order['params']['shipping_name']
            ];
        }

        return $result;
    }
}