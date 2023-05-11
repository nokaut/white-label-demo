<?php
namespace App\Lib\View\Data\Converter\Filters\Callback\PriceRanges;

use Nokaut\ApiKit\Collection\Products;
use Nokaut\ApiKit\Ext\Data\Collection\Filters\PriceRanges;
use Nokaut\ApiKit\Ext\Data\Converter\Filters\Callback\PriceRanges\CallbackInterface;
use Nokaut\ApiKit\Ext\Data\Entity\Filter\PriceRange;

/**
 * Created by PhpStorm.
 * User: jjuszkiewicz
 * Date: 08.10.2014
 * Time: 15:30
 */
class SetName implements CallbackInterface
{

    /**
     * @param PriceRanges $priceRanges
     * @param Products $products
     */
    public function __invoke(PriceRanges $priceRanges, Products $products)
    {
        foreach ($priceRanges as $priceRange) {
            $name = "";
            /** @var PriceRange $priceRange */
            if ($priceRange->getMin()) {
                $name .= "od " . number_format($priceRange->getMin(), 2, ',', ' ') . " ";
            }
            if ($priceRange->getMax()) {
                $name .= "do " . number_format($priceRange->getMax(), 2, ',', ' ');
            }
            $priceRange->setName(trim($name));
        }
    }
} 