<?php
/**
 * Created by PhpStorm.
 * User: jjuszkiewicz
 * Date: 15.10.2014
 * Time: 12:54
 */

namespace WL\AppBundle\Lib\View\Data\Converter\Filters\Callback\Categories;


use Nokaut\ApiKit\Collection\Products;
use Nokaut\ApiKit\Ext\Data\Collection\Filters\Categories;
use Nokaut\ApiKit\Ext\Data\Converter\Filters\Callback\Categories\CallbackInterface;
use Nokaut\ApiKit\Ext\Data\Entity\Filter\Category;

class ReduceIncorrectCategories implements CallbackInterface
{
    /**
     * @param Categories $categories
     * @param Products $products
     */
    public function __invoke(Categories $categories, Products $products)
    {
        $categoriesArray = $categories->getEntities();

        $categoriesArray = array_filter($categoriesArray, function($entity) use ($products) {
            /** @var Category $entity */
            return $entity->getIsFilter() || $entity->getTotal() < $products->getMetadata()->getTotal();
        });

        $categories->setEntities($categoriesArray);
    }

}