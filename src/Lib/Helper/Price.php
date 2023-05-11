<?php
/**
 * Created by PhpStorm.
 * User: jjuszkiewicz
 * Date: 29.03.2014
 * Time: 17:23
 */

namespace App\Lib\Helper;


use Nokaut\ApiKit\Entity\Product\Prices;

class Price
{

    public function getSavePercent(Prices $prices)
    {
        if ($prices->getMax() == 0) {
            return 0;
        }
        $percent = ($prices->getMax() - $prices->getMin()) / $prices->getMax() * 100;
        return number_format($percent);
    }
} 