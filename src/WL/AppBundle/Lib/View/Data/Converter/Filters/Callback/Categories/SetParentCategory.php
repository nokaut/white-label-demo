<?php
/**
 * Created by PhpStorm.
 * User: jjuszkiewicz
 * Date: 15.10.2014
 * Time: 08:00
 */

namespace WL\AppBundle\Lib\View\Data\Converter\Filters\Callback\Categories;



use Nokaut\ApiKit\Entity\Category;
use WL\AppBundle\Lib\CategoriesAllowed;

class SetParentCategory extends \Nokaut\ApiKit\Ext\Data\Converter\Filters\Callback\Categories\SetParentCategory
{
    /**
     * @var CategoriesAllowed
     */
    protected $categoriesAllowed;

    function __construct(Category $currentCategory, CategoriesAllowed $categoriesAllowed)
    {
        parent::__construct($currentCategory);
        $this->categoriesAllowed = $categoriesAllowed;
    }


    protected function prepareParentCategory($path)
    {
        if (in_array($path->getId(),$this->categoriesAllowed->getAllowedCategories())) {
            return parent::prepareParentCategory($path);
        }
        return null;
    }


} 