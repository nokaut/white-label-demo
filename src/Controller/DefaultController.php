<?php

namespace App\Controller;

use App\Lib\CategoriesAllowed;
use App\Lib\Filter\Controller\UrlCategoryFilter;
use App\Lib\Filter\PropertiesFilter;
use App\Lib\Repository\ProductsAsyncRepository;
use App\Lib\Repository\ProductsRepository;
use App\Lib\RepositoryFactory;
use App\Lib\Type\Breadcrumb;
use Nokaut\ApiKit\ClientApi\Rest\Fetch\ProductsFetch;
use Nokaut\ApiKit\ClientApi\Rest\Query\ProductsQuery;
use Nokaut\ApiKit\Collection\Products;
use Nokaut\ApiKit\Repository\CategoriesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DefaultController extends AbstractController
{
    public function __construct(
        private RepositoryFactory $repositoryFactory,
        private CategoriesAllowed $categoriesAllowed
    )
    {
    }

    public function indexAction()
    {
        $breadcrumbs = array();
        $breadcrumbs[] = new Breadcrumb("Strona główna");

        $productsGroupCategory = $this->createProductsGroupCategory();
        return $this->render('Default/index.html.twig', [
            'breadcrumbs' => $breadcrumbs,
            'productsGroupCategory' => $productsGroupCategory
        ]);
    }

    public function createProductsGroupCategory()
    {
        /** @var RepositoryFactory $productsAsyncRepo */
        $repositoryFactory = $this->repositoryFactory;

        $productsRepo = $repositoryFactory->getProductsAsyncRepository();

        $categoriesAllowed = $this->categoriesAllowed;

        if ($categoriesAllowed->isAllowedAllCategories()) {
            $productsFetchGroupCategory = $this->fetchProductsFromAllCategories($repositoryFactory);
        } else {
            $productsFetchGroupCategory = $this->fetchProductsFormAllowedCategories($categoriesAllowed, $productsRepo);
        }

        $productsRepo->fetchAllAsync();

        $productsGroupCategory = [];
        foreach ($productsFetchGroupCategory as $productsFetch) {
            /** @var ProductsFetch $productsFetch */
            $products = $productsFetch->getResult();
            $this->filter($products);
            $productsGroupCategory[] = $products;
        }
        return $productsGroupCategory;
    }

    /**
     * @param ProductsAsyncRepository $productsAsyncRepo
     * @param $categoriesIds
     * @return ProductsFetch
     */
    protected function fetchProducts(ProductsAsyncRepository $productsAsyncRepo, $categoriesIds)
    {
        $query = new ProductsQuery($this->getParameter('api_url'));
        $query->setCategoryIds($categoriesIds);
        $query->setFields(ProductsRepository::$fieldsForList);
        $query->setOrder('views', 'desc');
        $query->setLimit(18);
        return $productsAsyncRepo->fetchProductsWithBestOfferByQuery($query);
    }

    /**
     * @param Products $products
     */
    protected function filter($products)
    {
        if ($products) {
            $filterProperties = new PropertiesFilter();
            $filterProperties->filterProducts($products);

            $filterUrl = new UrlCategoryFilter();
            $filterUrl->filter($products);
        }
    }

    /**
     * @param $repositoryFactory
     * @return array
     */
    protected function fetchProductsFromAllCategories($repositoryFactory)
    {
        $productsFetchGroupCategory = [];
        /** @var CategoriesRepository $categoriesRepo */
        $categoriesRepo = $repositoryFactory->getCategoriesRepository();
        $productsRepo = $repositoryFactory->getProductsAsyncRepository();
        $categories = $categoriesRepo->fetchMenuCategories();

        foreach ($categories as $category) {
            if ($category->getId() == 9768) {//pominąć erotykę
                continue;
            }
            $productsFetchGroupCategory[] = $this->fetchProducts($productsRepo, [$category->getId()]);
        }
        return $productsFetchGroupCategory;
    }

    /**
     * @param $categoriesAllowed
     * @param $productsRepo
     * @param $productsFetchGroupCategory
     * @return array
     */
    protected function fetchProductsFormAllowedCategories($categoriesAllowed, $productsRepo)
    {
        $productsFetchGroupCategory = [];
        foreach ($categoriesAllowed->getParametersCategories() as $allowedGroupCategories) {
            $productsFetchGroupCategory[] = $this->fetchProducts($productsRepo, $allowedGroupCategories);
        }
        return $productsFetchGroupCategory;
    }
}
