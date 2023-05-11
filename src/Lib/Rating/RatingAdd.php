<?php
/**
 * Created by PhpStorm.
 * User: jjuszkiewicz
 * Date: 26.07.2014
 * Time: 16:56
 */

namespace App\Lib\Rating;


use App\Lib\Helper\Ip;
use Nokaut\ApiKit\Entity\Product\Rating\Rate;
use Nokaut\ApiKit\Repository\ProductsRepository;
use function App\Lib\Rating\setcookie;

class RatingAdd
{
    private $cookeExpire = 86400; //24 hours
    /**
     * @var ProductsRepository
     */
    private $productsRepository;

    /**
     * RatingAdd constructor.
     * @param ProductsRepository $productsRepository
     */
    function __construct($productsRepository)
    {
        $this->productsRepository = $productsRepository;
    }

    /**
     * @param $productId
     * @param $rate
     * @return \Nokaut\ApiKit\Entity\Product\Rating|false - Rrating or false if fail send rate
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

    /**
     * @param $productId
     * @param $rateValue
     * @return \Nokaut\ApiKit\Entity\Product\Rating
     */
    private function sendToApi($productId, $rateValue)
    {
        $rate = new Rate();
        $rate->setCreatedAt(date('Y-m-d'));
        $rate->setRate($rateValue);
        $rate->setIpAddress(Ip::getUserIp());
        return $this->productsRepository->createProductRate($productId, $rate);
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