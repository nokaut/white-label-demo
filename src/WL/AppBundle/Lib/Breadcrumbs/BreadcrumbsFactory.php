<?php
/**
 * Created by PhpStorm.
 * User: jjuszkiewicz
 * Date: 12.10.2016
 * Time: 15:35
 */

namespace WL\AppBundle\Lib\Breadcrumbs;


use WL\AppBundle\Lib\CategoriesAllowed;

class BreadcrumbsFactory
{
    public static function newInstance(CategoriesAllowed $categoriesAllowed)
    {
        if ($categoriesAllowed->isAllowedAllCategories()) {
            return new BreadcrumbsBuilder();
        }
        return new BreadcrumbsAllowedCategoriesBuilder($categoriesAllowed);
    }
}