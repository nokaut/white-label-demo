<?php
/**
 * Created by PhpStorm.
 * User: jjuszkiewicz
 * Date: 05.09.2014
 * Time: 12:43
 */

namespace App\Lib\Menu;


use App\Lib\CategoriesAllowed;
use App\Lib\Helper\Uri;
use App\Lib\RepositoryFactory;
use App\Lib\Type\Menu\Link;
use App\Lib\Type\MenuLink;
use Nokaut\ApiKit\ClientApi\Rest\Fetch\CategoriesFetch;
use Nokaut\ApiKit\ClientApi\Rest\Fetch\ProductsFetch;
use Nokaut\ApiKit\Collection\Categories;
use Nokaut\ApiKit\Entity\Category;
use App\Lib\Repository\ProductsAsyncRepository;

class MegaMenuBuilder implements MenuInterface
{

    private $template = 'megaMenu.html.twig';
    /**
     * @var MenuLink[]
     */
    protected $menuLinks = [];

    /**
     * @var ProductsAsyncRepository
     */
    protected $productsRepository;
    /**
     * @var \Nokaut\ApiKit\Repository\CategoriesAsyncRepository
     */
    protected $categoriesRepository;
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
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @return MenuLink[]
     */
    public function getMenuLinks()
    {
        if (empty($this->menuLinks)) {
            $this->buildMenu();
        }
        return $this->menuLinks;
    }

    /**
     * @return MenuLink[]
     */
    private function buildMenu()
    {
        $categoriesFetch = $this->fetchCategories();
        $productsByGroup = $this->fetchProducts();
        $this->productsRepository->fetchAllAsync();

        foreach ($this->categoriesAllowed->getParametersCategories() as $name => $groupedCategoriesIds) {
            $menuLink = new MenuLink($name);
            $this->setSubLinks($categoriesFetch->getResult(), $groupedCategoriesIds, $menuLink);
            $menuLink->setTopProducts($productsByGroup[$name]->getResult());
            $this->menuLinks[] = $menuLink;
        }
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
                    $link = new Link(Uri::prepareApiUrl($category->getUrl()), $category->getTitle());
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
            $result[$name] = $this->productsRepository->fetchTopProducts(6, $groupedCategoriesIds);
        }
        return $result;
    }
} 