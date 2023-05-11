<?php
/**
 * Created by PhpStorm.
 * User: jjuszkiewicz
 * Date: 12.10.2016
 * Time: 11:46
 */

namespace App\Lib\Menu;


use App\Lib\RepositoryFactory;
use App\Lib\Type\Menu\Link;
use Nokaut\ApiKit\ClientApi\Rest\Fetch\CategoriesFetch;
use Nokaut\ApiKit\Collection\Categories;
use Nokaut\ApiKit\Collection\Sort\CategoriesSort;
use Nokaut\ApiKit\Entity\Category;

class DropDownMenuBuilder implements MenuInterface
{
    private $template = 'dropDownMenu.html.twig';
    /**
     * @var Link[]
     */
    protected $menuLinks = [];

    /**
     * @var \Nokaut\ApiKit\Repository\CategoriesAsyncRepository
     */
    protected $categoriesRepository;
    /**
     * @var RepositoryFactory
     */
    protected $repositoryFactory;

    public function __construct(RepositoryFactory $repositoryFactory)
    {
        $this->repositoryFactory = $repositoryFactory;
        $this->categoriesRepository = $repositoryFactory->getCategoriesAsyncRepository();
    }

    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @return Link[]
     */
    public function getMenuLinks()
    {
        if (empty($this->menuLinks)) {
            $this->buildMenu();
        }
        return $this->menuLinks;
    }

    /**
     * @return Link[]
     */
    private function buildMenu()
    {
        $categoriesFetch = $this->fetchCategories();
        $this->categoriesRepository->fetchAllAsync();
        /** @var Categories $categories */
        $categories = $categoriesFetch->getResult();
        CategoriesSort::sortByTitle($categories);

        /** @var Category $category */
        foreach ($categories as $category) {
            $menuLink = new Link(ltrim($category->getUrl(), '/'), $category->getTitle());
            $this->menuLinks[] = $menuLink;
        }
    }

    /**
     * @return CategoriesFetch
     */
    protected function fetchCategories()
    {
        return $this->categoriesRepository->fetchMenuCategories();
    }

}