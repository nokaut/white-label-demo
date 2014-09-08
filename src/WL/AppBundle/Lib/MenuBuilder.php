<?php
/**
 * Created by PhpStorm.
 * User: jjuszkiewicz
 * Date: 05.09.2014
 * Time: 12:43
 */

namespace WL\AppBundle\Lib;


use Nokaut\ApiKit\Collection\Categories;
use Nokaut\ApiKit\Entity\Category;
use Nokaut\ApiKit\Repository\CategoriesRepository;
use WL\AppBundle\Lib\Type\Menu\Link;
use WL\AppBundle\Lib\Type\MenuLink;

class MenuBuilder
{
    /**
     * @var CategoriesRepository
     */
    protected static $categoriesRepository;
    /**
     * @var CategoriesAllowed
     */
    protected static $categoriesAllowed;


    public static function buildMenu(CategoriesRepository $categoriesRepository, CategoriesAllowed $categoriesAllowed)
    {
        self::$categoriesRepository = $categoriesRepository;
        self::$categoriesAllowed = $categoriesAllowed;
        $categories = self::fetchCategories();

        $menuLinks = array();
        foreach (self::$categoriesAllowed->getParametersCategories() as $name => $groupedCategoriesIds) {
            $menuLink = new MenuLink($name);
            self::setSubLinks($categories, $groupedCategoriesIds, $menuLink);
            $menuLinks[] = $menuLink;
        }
        return $menuLinks;
    }

    /**
     * @return Categories
     */
    protected static function fetchCategories()
    {
        return self::$categoriesRepository->fetchCategoriesByIds(self::$categoriesAllowed->getAllowedCategories());
    }

    /**
     * @param Categories $categories
     * @param array $groupedCategoriesIds
     * @param MenuLink $menuLink
     */
    private static function setSubLinks($categories, array $groupedCategoriesIds, MenuLink $menuLink)
    {
        foreach ($groupedCategoriesIds as $categoryId) {
            foreach ($categories as $category) {
                /** @var Category $category */
                if ($category->getId() == $categoryId) {
                    $link = new Link($category->getUrl(), $category->getTitle());
                    $menuLink->addSubLinks($link);
                    break;
                }
            }
        }
    }
} 