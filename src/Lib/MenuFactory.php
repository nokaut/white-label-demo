<?php
/**
 * Created by PhpStorm.
 * User: jjuszkiewicz
 * Date: 12.10.2016
 * Time: 11:30
 */

namespace App\Lib;

use App\Lib\Menu\MenuInterface;

class MenuFactory
{
    public static function newInstance(CategoriesAllowed $categoriesAllowed, MenuInterface $megaMenu, MenuInterface $dropDownMenu)
    {
        if ($categoriesAllowed->isAllowedAllCategories()) {
            return $dropDownMenu;
        }
        return $megaMenu;
    }
}
