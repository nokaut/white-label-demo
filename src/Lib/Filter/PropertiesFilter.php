<?php
/**
 * Created by PhpStorm.
 * User: jjuszkiewicz
 * Date: 24.07.2014
 * Time: 20:17
 */

namespace App\Lib\Filter;


use Nokaut\ApiKit\Collection\Products;
use Nokaut\ApiKit\Entity\Product;

class PropertiesFilter
{

    public static $uselessPropertiesName = array('producent', 'ean', 'waga');

    /**
     * @param Product $product
     */
    public function filterProduct(Product $product)
    {
        if (!$product->getProperties()) {
            $product->setProperties(array());
        }

        $properties = array();
        foreach ($product->getProperties() as $property) {
            if (!in_array(mb_strtolower($property->getName(), 'UTF8'), self::$uselessPropertiesName)) {
                $properties[] = $property;
            }
        }
        $product->setProperties($properties);
    }

    /**
     * @param Products $products
     * @return Products
     */
    public function filterProducts(Products $products)
    {
        foreach ($products as $product) {
            $this->filterProduct($product);
        }
    }

} 