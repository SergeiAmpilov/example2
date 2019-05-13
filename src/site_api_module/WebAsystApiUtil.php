<?php

namespace SiteApiModule;

use MsApiModule\MSApi;

class WebAsystApiUtil
{

    public static function UpdateOrder($positions, $orderId, $waApi, $msApi)
    {
        $items = [];
        foreach ($positions as $row) {
            $product = $msApi->getInfo($row->assortment->meta->href);
            $productId = $product->code;
            if (strlen($product->code) > 5) {
                foreach (explode('s', $product->code) as $code) {
                    try {
                        $waApi->shopOrderGetInfo($code);
                        $productId = $code;
                        break;
                    } catch (\Exception $e) {
                        continue;
                    }
                }
            }
            $items[] = [
                'product_id' => $productId,
                'sku_id' => self::getSku($productId, $product->article, $waApi),
                'quantity' => $row->quantity,
                'price' => $row->price / 100
            ];
        }
        $params = [
            'id' => $orderId,
            'items' => $items,
        ];
        $result = $waApi->shopOrderSave(urldecode(http_build_query($params)));
        if ($result['status'] == 'fail') {
            throw new \Exception('Ни один id продукта не подходит ' . $product->code);
        }


    }

    public static function getSku($productId, $skuCode, $api)
    {
        $result = $api->shopProductSkusGetList($productId);
        if (isset($result['error']) && $result['error'] == 'invalid_param') {
            throw new \Exception('Неправильный product id ' . $productId);
        } elseif (isset($result['status']) && $result['status'] == 'fail') {
            throw new \Exception('Неправильный product id (статус фэйл)' . $productId);
        }
        foreach ($result as $sku) {
            if ($sku['sku'] == $skuCode) {
                return $sku['id'];
            }
        }
        throw new \Exception('Ни один sku продукта: ' . $productId . ' не подходит к ' . $skuCode);
    }
}