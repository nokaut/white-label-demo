<?php
/**
 * Created by PhpStorm.
 * User: jjuszkiewicz
 * Date: 15.10.2014
 * Time: 08:00
 */

namespace App\Lib\View\Data\Converter\Filters\Callback\Categories;



use App\Lib\CategoriesAllowed;
use Nokaut\ApiKit\Collection\Products;
use Nokaut\ApiKit\Entity\Category;
use Nokaut\ApiKit\Entity\Category\Path;
use Nokaut\ApiKit\Ext\Data\Collection\Filters\Categories;
use Nokaut\ApiKit\Ext\Data\Entity\Filter\ParentCategory;

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


    /**
     * @param Categories $categories
     * @param Products $products
     */
    public function __invoke(Categories $categories, Products $products)
    {
        $pathList = $this->currentCategory->getPath();

        if($this->isAllowedParentCategory($pathList)) {
            foreach ($pathList as $item) {
                if ($item->getId() == $this->currentCategory->getParentId()) {
                    $parentCategory = $this->prepareParentCategory($item);
                    $categories->setParentCategory($parentCategory);
                    return;
                }
            }
        }
        if (!$categories->getParentCategory()) {
            $parentCategory = new ParentCategory();
            $parentCategory->setUrl('/');
            $parentCategory->setName('Strona główna');
            $categories->setParentCategory($parentCategory);
        }
    }

    /**
     * @param Path[] $pathList
     * @return bool
     */
    protected function isAllowedParentCategory($pathList)
    {
        if ($this->categoriesAllowed->isAllowedAllCategories()) {
            return true;
        }

        foreach ($pathList as $path) {
            if (in_array($path->getId(), $this->categoriesAllowed->getAllowedCategories())) {
                return true;
            }
            if ($this->currentCategory->getParentId() == $path->getId()) {
                return false;
            }
        }
        return false;
    }


} 