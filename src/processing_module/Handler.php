<?php

namespace ProcessingModule;

use MsApiModule\MSApi;

class Handler
{
    /**
     * Проверяет, есть ли в файле соответствия ид клиента в моем складе
     * @static
     * @param string $id ид клиента на сайте
     * @param string $type тип сущности [customer, product, order]
     * @param MSApi $api обьект апи моего склада
     * @return string ид клиента в моем складе или 'false'
     * @throws \Exception
     */
    public static function checkMsId($id, $type, $api)
    {
        $types = [
            'customer' => 'counterparty',
            'product' => 'product',
            'order'=> 'customerorder'
        ];
        $result = unserialize(file_get_contents('configs/' . $type . 'sIds.txt'));
        if (array_key_exists($id, $result) && !empty($result[$id])) {
            $href = $result[$id]['href'];
            $uuidHref = $result[$id]['uuidHref'];
            $result = $api->getOne($types[$type], $href);
            if (isset($result->errors)) {
                $resultSecond = $api->getOne($type, $uuidHref);
                if (isset($resultSecond->errors)) {
                    throw new \Exception($type . ' с href: ' . $href . ' и ' . $uuidHref . ' не найден');
                }
                $resultId = $resultSecond->id;
                return $resultId;
            }
            $resultId = $result->id;
            return $resultId;
        } else {
            return 'false';
        }
    }
}