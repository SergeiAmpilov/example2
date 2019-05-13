<?php

namespace SiteApiModule;

use SiteApiModule\Exception\CurlExecException;
use SiteApiModule\Exception\InvalidTokenException;

/**
 * Используется для доступа к Апи webasyst
 * Class WebasystApi
 * @package SiteApiModule
 */
class WebasystApi
{

    /**
     * @var string $token токен для совершения запроса
     */
    private $token;

    /**
     * @var string $domain домен в webasyst
     */
    private $domain;

    /**
     * WebasystApi constructor.
     * @param string $token токен для совершения запроса
     * @param string $domain домен в webasyst
     */
    public function __construct($token,$domain)
    {
        $this->token = $token;
        $this->domain = $domain;
    }

    /**
     * Делает curl запрос по указанному адресу и возвращает результат
     * @param string $url алрес, по которому нужно сделать запрос
     * @param bool $is_array тип возвращаемого значения
     * @param array $postFields если метод post, сюда добавлюяется поля post
     * @param string $type тим метода запроса GET | POST
     * @return object stdClass обьект с результатом запросом
     * @throws CurlExecException
     */
    private function curl($url, $is_array, $postFields = array(), $type = "GET")
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $type);
        if ($type == "POST") {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $postFields);
        }
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        try {
            return json_decode(curl_exec($curl), $is_array);
        } catch (\Exception $e) {
            throw new CurlExecException($e->getMessage());
        }
    }

    /**
     * Метод обертка для курла, формирует ссылку, вызывет curl, получает массив заказов
     * @return object stdClass обьект, содержащий массив 100 последних заказов
     * @throws CurlExecException
     * @throws InvalidTokenException
     */
    public function shopOrderSearch()
    {
        try {
            $result = $this->curl('https://' . $this->domain . '/api.php?app=shop&method=order.search&access_token=' . $this->token,
                false);
            if (isset($result->error) && $result->error == 'invalid_token') {
                throw new InvalidTokenException('Invalid token');
            }
            return $result;
        } catch (CurlExecException $e) {
            throw new CurlExecException($e->getMessage());
        } catch (InvalidTokenException $e) {
            throw new InvalidTokenException($e->getMessage());
        }
    }

    /**
     * @param string $id
     * @return array
     * @throws CurlExecException
     * @throws InvalidTokenException
     */
    public function shopOrderGetInfo($id)
    {
        try {
            $result = $this->curl('https://' . $this->domain . '/api.php?app=shop&method=order.getInfo&id=' . $id .
                '&access_token=' . $this->token, true);
            if (isset($result->error) && $result->error == 'invalid_token') {
                throw new InvalidTokenException('Invalid token');
            }
            return $result;
        } catch (CurlExecException $e) {
            throw new CurlExecException($e->getMessage());
        } catch (InvalidTokenException $e) {
            throw new InvalidTokenException($e->getMessage());
        }
    }

    /**
     * @param $params
     * @return object
     * @throws CurlExecException
     * @throws InvalidTokenException
     */
    public function shopOrderSave($params)
    {
        try {
            $result = $this->curl('https://' . $this->domain . '/api.php?app=shop&method=order.save&access_token='
                . $this->token, true, $params, 'POST');
            if (isset($result->error) && $result->error == 'invalid_token') {
                throw new InvalidTokenException('Invalid token');
            }
            return $result;

        } catch (CurlExecException $e) {
            throw new CurlExecException($e->getMessage());
        } catch (InvalidTokenException $e) {
            throw new InvalidTokenException($e->getMessage());
        }
    }

    public function shopProductSkusGetList($productId)
    {
        try {
            $result = $this->curl('https://' . $this->domain . '/api.php?app=shop&method=product.skus.getList&product_id=' . $productId . '&access_token='
                . $this->token, true);
            if (isset($result->error) && $result->error == 'invalid_token') {
                throw new InvalidTokenException('Invalid token');
            }
            return $result;
        } catch (CurlExecException $e) {
            throw new CurlExecException($e->getMessage());
        } catch (InvalidTokenException $e) {
            throw new InvalidTokenException($e->getMessage());
        }
    }

    public function shopOrderComplete($productId)
    {
        try {
            $params = [
                'id' => $productId
            ];
            $result = $this->curl('https://' . $this->domain . '/api.php?app=shop&method=order.complete&access_token='
                . $this->token, true, $params, 'POST');
            if (isset($result->error) && $result->error == 'invalid_token') {
                throw new InvalidTokenException('Invalid token');
            }
            return $result;
        } catch (CurlExecException $e) {
            throw new CurlExecException($e->getMessage());
        } catch (InvalidTokenException $e) {
            throw new InvalidTokenException($e->getMessage());
        }
    }

    public function shopOrderAction($orderId, $action)
    {
        try {
            $params = [
                'id' => $orderId,
                'action' => $action
            ];
            $result = $this->curl('https://' . $this->domain . '/api.php?app=shop&method=order.action&access_token='
                . $this->token, true, $params, 'POST');
            if (isset($result->error) && $result->error == 'invalid_token') {
                throw new InvalidTokenException('Invalid token');
            }
            return $result;
        } catch (CurlExecException $e) {
            throw new CurlExecException($e->getMessage());
        } catch (InvalidTokenException $e) {
            throw new InvalidTokenException($e->getMessage());
        }
    }

    /**
     * @param int $orderId ид заказв
     * @param array $contact массив с данными покупателя
     * @return object
     * @throws CurlExecException
     * @throws InvalidTokenException
     */
    public function shopOrderContactSave($orderId, $contact)
    {
        try {
            $params = [
                'order_id' => $orderId,
                'contact' => $contact
            ];
            $result = $this->curl('https://' . $this->domain . '/api.php?app=shop&method=order.contactSave&access_token='
                . $this->token, true, http_build_query($params), 'POST');
            if (isset($result->error) && $result->error == 'invalid_token') {
                throw new InvalidTokenException('Invalid token');
            }
            return $result;
        } catch (CurlExecException $e) {
            throw new CurlExecException($e->getMessage());
        } catch (InvalidTokenException $e) {
            throw new InvalidTokenException($e->getMessage());
        }
    }
}