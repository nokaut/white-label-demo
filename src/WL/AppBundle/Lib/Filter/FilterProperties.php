<?php
/**
 * Created by PhpStorm.
 * User: jjuszkiewicz
 * Date: 24.07.2014
 * Time: 20:17
 */

namespace WL\AppBundle\Lib\Filter;


use Nokaut\ApiKit\Collection\Products;
use Nokaut\ApiKit\Entity\Product;

class FilterProperties
{

    public static $uselessPropertiesName = array('producent', 'ean', 'waga');

    /**
     * @param Product\Property[] $productProperties
     * @return Product\Property[]
     */
    public function filterProperties($productProperties)
    {
        $properties = array();
        foreach ($productProperties as $property) {
            if (!in_array(mb_strtolower($property->getName(), 'UTF8'), self::$uselessPropertiesName)) {
                $properties[] = $property;
            }
        }
        return $properties;
    }

    /**
     * @param Products $products
     * @return Products
     */
    public function filterPropertiesInProducts(Products $products)
    {
        foreach ($products as $product) {
            /** @var Product $product */
            $product->setProperties(
                $this->filterProperties($product->getProperties())
            );
        }
        return $products;
    }

} 