<?php

namespace WL\AppBundle\Controller;

use Nokaut\ApiKit\ClientApi\Rest\Fetch\ProductsFetch;
use Nokaut\ApiKit\ClientApi\Rest\Query\ProductsQuery;
use Nokaut\ApiKit\Collection\Products;
use Nokaut\ApiKit\Repository\ProductsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use WL\AppBundle\Lib\CategoriesAllowed;
use WL\AppBundle\Lib\Filter\FilterProperties;
use WL\AppBundle\Lib\Pagination\Pagination;
use WL\AppBundle\Lib\Type\Breadcrumb;
use WL\AppBundle\Lib\Repository\ProductsAsyncRepository;

class DefaultController extends Controller
{
    public function indexAction()
    {

        $breadcrumbs = array();
        $breadcrumbs[] = new Breadcrumb("Strona główna");


        /** @var ProductsAsyncRepository $productsAsyncRepo */
        $productsAsyncRepo = $this->get('repo.products.async');
        $products24Fetch = $this->fetchRandProducts($productsAsyncRepo);
        $productsTop10Fetch = $productsAsyncRepo->fetchTopProducts();
        $productsAsyncRepo->fetchAllAsync();


        return $this->render('WLAppBundle:Default:index.html.twig',
            array(
                'breadcrumbs' => $breadcrumbs,
                'products24' => $this->filterProducts($products24Fetch),
                'productsTop10' => $productsTop10Fetch->getResult()
            ));
    }

    /**
     * @param ProductsAsyncRepository $productsAsyncRepo
     * @return ProductsFetch
     */
    protected function fetchRandProducts(ProductsAsyncRepository $productsAsyncRepo)
    {
        $query = new ProductsQuery($this->container->getParameter('api_url'));
        /** @var CategoriesAllowed $categoriesAllowed */
        $categoriesAllowed = $this->get('categories.allowed');
        $query->setCategoryIds($categoriesAllowed->getAllowedCategories());
        $query->setFields(ProductsRepository::$fieldsForList);
        $query->setOrder('random', 'asc');
        $query->setLimit(24);
        return $productsAsyncRepo->fetchProductsWithBestOfferByQuery($query);
    }

    /**
     * @param ProductsFetch $products24Fetch
     * @return mixed
     */
    protected function filterProducts($products24Fetch)
    {
        /** @var Products $products */
        $products = $products24Fetch->getResult();
        if ($products) {
            $filterProperties = new FilterProperties();
            return $filterProperties->filterPropertiesInProducts($products);
        }
        return $products;
    }

    /**
     * @param ProductsFetch $productsFetch
     * @return Pagination
     */
    protected function preparePagination($productsFetch)
    {
        /** @var Products|null $products */
        $products = $productsFetch->getResult();
        if (!$products) {
            return new Pagination();
        }
        $pagination = new Pagination();
        $pagination->setTotal($products->getMetadata()->getPaging()->getTotal());
        $pagination->setCurrentPage($products->getMetadata()->getPaging()->getCurrent());
        $pagination->setUrlTemplate(
            $products->getMetadata()->getPaging()->getUrlTemplate()
        );
        return $pagination;
    }
}
