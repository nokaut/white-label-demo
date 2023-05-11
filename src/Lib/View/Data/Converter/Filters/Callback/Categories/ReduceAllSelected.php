<?php
/**
 * Created by PhpStorm.
 * User: jjuszkiewicz
 * Date: 13.10.2014
 * Time: 14:24
 */

namespace App\Lib\View\Data\Converter\Filters\Callback\Categories;


use Nokaut\ApiKit\Collection\Products;
use Nokaut\ApiKit\Ext\Data\Collection\Filters\Categories;
use Nokaut\ApiKit\Ext\Data\Converter\Filters\Callback\Categories\CallbackInterface;

class ReduceAllSelected implements CallbackInterface
{
    /**
     * @param Categories $categories
     * @param Products $products
     */
    public function __invoke(Categories $categories, Products $products)
    {
        if ($this->isSelectedAllAllowedCategories($categories)) {
            $categories->setEntities(array());
        }
    }

    protected function isSelectedAllAllowedCategories(Categories $categories)
    {
        if (count($categories) == 1) {
            return false;
        }

        return true;
    }
} 