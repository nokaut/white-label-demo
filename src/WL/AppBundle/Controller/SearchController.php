<?php

namespace WL\AppBundle\Controller;

use Nokaut\ApiKit\ClientApi\Rest\Async\ProductsAsyncFetch;
use Nokaut\ApiKit\ClientApi\Rest\Query\ProductsQuery;
use Nokaut\ApiKit\Collection\Products;
use Nokaut\ApiKit\Entity\Product;
use Nokaut\ApiKit\Repository\ProductsAsyncRepository;
use Nokaut\ApiKit\Repository\ProductsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use WL\AppBundle\Lib\Filter\FilterProperties;
use WL\AppBundle\Lib\Pagination\Pagination;
use WL\AppBundle\Lib\Type\Breadcrumb;
use WL\AppBundle\Lib\Type\Filter;

class SearchController extends Controller
{
    public function indexAction($phrase)
    {
        $phrase = $this->repairPhrase($phrase);
        /** @var ProductsAsyncRepository $productsRepo */
        $productsRepo = $this->get('repo.products.async');
        $productsFetch = $productsRepo->fetchProductsByUrl($phrase, ProductsRepository::$fieldsForList, 24);
        $productsTopFetch = $this->fetchTopProducts($productsRepo);
        $productsRepo->fetchAllAsync();
        /** @var Products $products */
        $products = $productsFetch->getResult();
        $pagination = $this->preparePagination($products);

        $breadcrumbs = array();
        $breadcrumbs[] = new Breadcrumb("Szukaj: " . $products->getMetadata()->getQuery()->getPhrase());

        $filters = $this->getFilters($products);
        $this->setCategoryToProduct($products);

        return $this->render('WLAppBundle:Search:index.html.twig', array(
            'products' => $this->filterProducts($productsFetch),
            'phrase' => $products ? $products->getMetadata()->getQuery()->getPhrase() : '',
            'breadcrumbs' => $breadcrumbs,
            'subcategories' => $products ? $products->getCategories() : array(),
            'filters' => $filters,
            'sorts' => $products ? $products->getMetadata()->getSorts() : array(),
            'pagination' => $pagination,
            'url' => $products ? $products->getMetadata()->getUrl() : '',
            'productsTop10' => $productsTopFetch->getResult()
        ));
    }

    /**
     * @param Products $products
     * @return Filter[]
     */
    protected function getFilters($products)
    {
        if (is_null($products)) {
            return array();
        }

        $filters = array();
        foreach ($products->getProducers() as $producer) {
            if ($producer->getIsFilter()) {
                $filter = new Filter();
                $filter->setName("Producent");
                $filter->setValue($producer->getName());
                $filter->setOutUrl($producer->getUrl());
                $filters[] = $filter;
            }
        }
        foreach ($products->getPrices() as $price) {
            if ($price->getIsFilter()) {
                $filter = new Filter();
                $filter->setName("Cena");
                $filter->setValue("od " . $price->getMin() . " do " . $price->getMax());
                $filter->setOutUrl($price->getUrl());
                $filters[] = $filter;
            }
        }
        return $filters;
    }

    protected function setCategoryToProduct(Products $products)
    {
        foreach ($products as $product) {
            /** @var Product $product */
            foreach ($products->getCategories() as $category) {
                if ($product->getCategoryId() == $category->getId()) {
                    $product->setCategory($category);
                    break;
                }
            }
        }
    }

    /**
     * @param string $phrase
     * @return string
     */
    private function repairPhrase($phrase)
    {
        $phrase = str_replace(
            array('ę', 'ó', 'ą', 'ś', 'ł', 'ż', 'ź', 'ć', 'ń'),
            array('e', 'o', 'a', 's', 'l', 'z', 'z', 'c', 'n'),
            $phrase);
        $phrase = preg_replace('/\s+/', ' ', $phrase);
        return $phrase;
    }

    /**
     * @param Products|null $products
     * @return Pagination
     */
    private function preparePagination($products)
    {
        if (is_null($products)) {
            return new Pagination();
        }
        $pagination = new Pagination();
        $pagination->setTotal($products->getMetadata()->getPaging()->getTotal());
        $pagination->setCurrentPage($products->getMetadata()->getPaging()->getCurrent());
        $pagination->setUrlTemplate(
            $this->get('router')->generate('search', array('phrase' => ltrim($products->getMetadata()->getPaging()->getUrlTemplate(), '/')))
        );
        return $pagination;
    }

    /**
     * @param ProductsAsyncRepository $productsAsyncRepo
     * @return ProductsAsyncFetch
     */
    protected function fetchTopProducts(ProductsAsyncRepository $productsAsyncRepo)
    {
        $query = new ProductsQuery($this->container->getParameter('api_url'));
        $query->setLimit(10);
        $query->setFields(ProductsRepository::$fieldsForProductBox);
        return $productsAsyncRepo->fetchProductsByQuery($query);
    }

    /**
     * @param ProductsAsyncFetch $productsFetch
     * @return mixed
     */
    protected function filterProducts($productsFetch)
    {
        /** @var Products $products */
        $products = $productsFetch->getResult();
        if ($products) {
            $filterProperties = new FilterProperties();
            return $filterProperties->filterPropertiesInProducts($products);
        }
        return $products;
    }

}
