<?php
/**
 * Created by PhpStorm.
 * User: jjuszkiewicz
 * Date: 12.10.2016
 * Time: 15:35
 */

namespace App\Lib\Breadcrumbs;


use App\Lib\CategoriesAllowed;

class BreadcrumbsBuilderFactory
{
    public static function newInstance(CategoriesAllowed $categoriesAllowed)
    {
        if ($categoriesAllowed->isAllowedAllCategories()) {
            return new BreadcrumbsBuilder();
        }
        return new BreadcrumbsAllowedCategoriesBuilder($categoriesAllowed);
    }
}