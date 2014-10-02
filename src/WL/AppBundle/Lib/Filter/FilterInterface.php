<?php
/**
 * Created by PhpStorm.
 * User: jjuszkiewicz
 * Date: 02.10.2014
 * Time: 10:14
 */

namespace WL\AppBundle\Lib\Filter;


use Nokaut\ApiKit\Collection\Products;

interface FilterInterface {

    /**
     * @param Products $products
     */
    public function filter(Products $products);
} 