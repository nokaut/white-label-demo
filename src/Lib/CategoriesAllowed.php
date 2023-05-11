<?php
/**
 * Created by PhpStorm.
 * User: jjuszkiewicz
 * Date: 05.09.2014
 * Time: 11:16
 */

namespace App\Lib;


use App\Lib\Exception\CategoryNotAllowedException;
use Nokaut\ApiKit\Entity\Category;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CategoriesAllowed extends AbstractController
{
    function __construct(private $parametersCategories)
    {
    }

    /**
     * @return bool
     */
    public function isAllowedAllCategories()
    {
        return $this->parametersCategories == null;
    }

    /**
     * @return array|false
     */
    public function getAllowedCategories()
    {
        if ($this->isAllowedAllCategories()) {
            return false;
        }
        $allowedCategories = array();

        foreach ($this->parametersCategories as $groupCategories) {
            $allowedCategories = array_merge($allowedCategories, $groupCategories);
        }
        return $allowedCategories;
    }

    /**
     * @return array
     */
    public function getParametersCategories()
    {
        return $this->parametersCategories;
    }

    /**
     * @param Category $category
     * @throws CategoryNotAllowedException
     */
    public function checkAllowedCategory(Category $category)
    {
        if ($this->isAllowedAllCategories()) {
            return;
        }

        $allowedCategoriesIds = $this->getAllowedCategories();
        foreach ($category->getPath() as $path) {
            if (in_array($path->getId(), $allowedCategoriesIds)) {
                return;
            }
        }

        throw new CategoryNotAllowedException("not allowed category " . $category->getTitle() . " ID " . $category->getId());
    }
} 