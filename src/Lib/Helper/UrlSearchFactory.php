<?php
/**
 * Created by PhpStorm.
 * User: jjuszkiewicz
 * Date: 12.10.2016
 * Time: 16:13
 */

namespace App\Lib\Helper;


use App\Lib\CategoriesAllowed;
use Nokaut\ApiKit\Repository\CategoriesRepository;

class UrlSearchFactory
{
    public static function newInstance(CategoriesAllowed $categoriesAllowed, CategoriesRepository $categoriesRepository)
    {
        if ($categoriesAllowed->isAllowedAllCategories()) {
            return new UrlSearch();
        }
        return new UrlSearchAllowedCategories($categoriesRepository, $categoriesAllowed);
    }
}