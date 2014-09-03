<?php
/**
 * Created by PhpStorm.
 * User: jjuszkiewicz
 * Date: 26.07.2014
 * Time: 16:56
 */

namespace WL\AppBundle\Lib\Rating;

use Guzzle\Http\Client;

class RatingAdd
{
    private $cookeExpire = 86400; //24 hours
    private $apiUrl;

    function __construct($apiUrl)
    {
        $this->apiUrl = $apiUrl;
    }

    /**
     * @param $productId
     * @param $rate
     * @return false|int - current rating or false if fail send rate
     */
    public function addRating($productId, $rate)
    {
        if (!self::canAddRate($productId)) {
            return false;
        }
        $currentRating = $this->sendToApi($productId, $rate);
        setcookie($this->prepareCookieKey($productId), 1, time()+$this->cookeExpire);
        return $currentRating;

    }

    private function sendToApi($productId, $rate)
    {
        $client = new Client();
        $post = '{"rate": '. intval($rate) .'}';
        $request = $client->post($this->apiUrl . "products/{$productId}/rate", null, $post);
        $request->addHeader('Content-Type', 'application/json');

        $response = $client->send($request);

        $bodyAsString = $response->getBody(true);
        if (empty($bodyAsString)) {
            return false;
        }
        $bodyObject = json_decode($bodyAsString);
        return $bodyObject->current_rating;
    }

    /**
     * @param $productId
     * @return bool
     */
    public static function canAddRate($productId)
    {
        return empty($_COOKIE[self::prepareCookieKey($productId)]);
    }

    /**
     * @param $productId
     * @return string
     */
    private static function prepareCookieKey($productId)
    {
        return 'rate_product_' . $productId;
    }
} 