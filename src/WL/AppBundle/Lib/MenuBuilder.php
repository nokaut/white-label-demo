<?php
/**
 * Created by PhpStorm.
 * User: jjuszkiewicz
 * Date: 05.09.2014
 * Time: 12:43
 */

namespace WL\AppBundle\Lib;


use Nokaut\ApiKit\ClientApi\Rest\Fetch\CategoriesFetch;
use Nokaut\ApiKit\ClientApi\Rest\Fetch\ProductsFetch;
use Nokaut\ApiKit\Collection\Categories;
use Nokaut\ApiKit\Entity\Category;
use WL\AppBundle\Lib\Type\Menu\Link;
use WL\AppBundle\Lib\Type\MenuLink;

class MenuBuilder
{
    /**
     * @var RepositoryFactory
     */
    protected $repositoryFactory;
    /**
     * @var CategoriesAllowed
     */
    protected $categoriesAllowed;

    public function __construct(RepositoryFactory $repositoryFactory, CategoriesAllowed $categoriesAllowed)
    {
        $this->repositoryFactory = $repositoryFactory;
        $this->productsRepository = $repositoryFactory->getProductsAsyncRepository();
        $this->categoriesRepository = $repositoryFactory->getCategoriesAsyncRepository();
        $this->categoriesAllowed = $categoriesAllowed;
    }

    /**
     * @return MenuLink[]
     */
    public function buildMenu()
    {
        $categoriesFetch = $this->fetchCategories();
        $productsByGroup = $this->fetchProducts();
        $this->productsRepository->fetchAllAsync();

        $menuLinks = array();
        foreach ($this->categoriesAllowed->getParametersCategories() as $name => $groupedCategoriesIds) {
            $menuLink = new MenuLink($name);
            $this->setSubLinks($categoriesFetch->getResult(), $groupedCategoriesIds, $menuLink);
            $menuLink->setTopProducts($productsByGroup[$name]->getResult());
            $menuLinks[] = $menuLink;
        }
        return $menuLinks;
    }

    /**
     * @return CategoriesFetch
     */
    protected function fetchCategories()
    {
        return $this->categoriesRepository->fetchCategoriesByIds($this->categoriesAllowed->getAllowedCategories());
    }

    /**
     * @param Categories $categories
     * @param array $groupedCategoriesIds
     * @param MenuLink $menuLink
     */
    private function setSubLinks($categories, array $groupedCategoriesIds, MenuLink $menuLink)
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

    /**
     * @return ProductsFetch[]
     */
    private function fetchProducts()
    {
        $result = array();
        foreach ($this->categoriesAllowed->getParametersCategories() as $name => $groupedCategoriesIds) {
            $result[$name] = $this->productsRepository->fetchProductsWithCategory(6, $groupedCategoriesIds);
        }
        return $result;
    }
} 