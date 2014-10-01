<?php

namespace WL\AppBundle\Controller;

use Nokaut\ApiKit\ClientApi\Rest\Fetch\ProductsFetch;
use Nokaut\ApiKit\ClientApi\Rest\Exception\NotFoundException;
use Nokaut\ApiKit\Collection\Products;
use Nokaut\ApiKit\Entity\Category;
use Nokaut\ApiKit\Entity\Metadata\Facet\PriceFacet;
use Nokaut\ApiKit\Repository\CategoriesRepository;
use Nokaut\ApiKit\Repository\ProductsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use WL\AppBundle\Lib\BreadcrumbsBuilder;
use WL\AppBundle\Lib\Filter\FilterProperties;
use WL\AppBundle\Lib\Pagination\Pagination;
use WL\AppBundle\Lib\Type\Filter;
use WL\AppBundle\Lib\Repository\ProductsAsyncRepository;

class CategoryController extends Controller
{
    public function indexAction($categoryUrlWithFilters)
    {
        $category = $this->fetchCategory($categoryUrlWithFilters);

        /** @var ProductsAsyncRepository $productsAsyncRepo */
        $productsAsyncRepo = $this->get('repo.products.async');
        $productsFetch = $productsAsyncRepo->fetchProductsByUrl($categoryUrlWithFilters, $this->getProductFields(), 24);
        $productsTopFetch = $productsAsyncRepo->fetchTopProducts(10, array($category->getId()));
        $productsAsyncRepo->fetchAllAsync();

        /** @var Products $products */
        $products = $productsFetch->getResult();

        $pagination = $this->preparePagination($products);

        $filters = $this->getFilters($products);

        $breadcrumbs = $this->prepareBreadcrumbs($category, $filters);

        $responseStatus = null;
        if ($products->getMetadata()->getTotal() == 0) {
            $responseStatus = new Response('', 404);
        }

        return $this->render('WLAppBundle:Category:index.html.twig', array(
            'category' => $category,
            'products' => $this->filterProducts($productsFetch),
            'productsTop10' => $productsTopFetch->getResult(),
            'breadcrumbs' => $breadcrumbs,
            'pagination' => $pagination,
            'subcategories' => $products ? $products->getCategories() : array(),
            'filters' => $filters,
            'sorts' => $products ? $products->getMetadata()->getSorts() : array(),
            'url' => $products ? $products->getMetadata()->getUrl() : ''
        ), $responseStatus);
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
                $filter->setName("Ceny");
                $filter->setValue($this->prepareFilterPriceValue($price));
                $filter->setOutUrl($price->getUrl());
                $filters[] = $filter;
            }
        }
        foreach ($products->getProperties() as $property) {
            foreach ($property->getValues() as $value) {
                if ($value->getIsFilter()) {
                    $filter = new Filter();
                    $filter->setName($property->getName());
                    $filter->setValue($value->getName());
                    $filter->setOutUrl($value->getUrl());
                    $filters[] = $filter;
                }
            }
        }
        return $filters;
    }

    /**
     * @param Products $products
     * @return Pagination
     */
    protected function preparePagination($products)
    {
        if (is_null($products)) {
            return new Pagination();
        }
        $pagination = new Pagination();
        $pagination->setTotal($products->getMetadata()->getPaging()->getTotal());
        $pagination->setCurrentPage($products->getMetadata()->getPaging()->getCurrent());
        $pagination->setUrlTemplate($products->getMetadata()->getPaging()->getUrlTemplate());
        $pagination->setUrlTemplate(
            $this->get('router')->generate('category', array('categoryUrlWithFilters' => ltrim($products->getMetadata()->getPaging()->getUrlTemplate(), '/')))
        );
        return $pagination;
    }

    /**
     * @param ProductsFetch $productsFetch
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

    /**
     * @param $categoryUrlWithFilters
     * @return Category
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    protected function fetchCategory($categoryUrlWithFilters)
    {
        $path = explode('/', $categoryUrlWithFilters);
        $categoryUrl = $path[0];
        /** @var CategoriesRepository $categoriesRepo */
        $categoriesRepo = $this->get('repo.categories');
        try {
            $category = $categoriesRepo->fetchByUrl($categoryUrl);
            return $category;
        } catch (NotFoundException $e) {
            throw $this->createNotFoundException("not found category " . $categoryUrl);
        }
    }

    /**
     * @param $category
     * @param Filter[] $filters
     * @return array
     */
    protected function prepareBreadcrumbs($category, array $filters)
    {
        /** @var BreadcrumbsBuilder $breadcrumbsBuilder */
        $breadcrumbsBuilder = $this->get('breadcrumb.builder');
        $breadcrumbs = $breadcrumbsBuilder->prepareBreadcrumbs(
            $category,
            function ($url) {
                return $this->get('router')->generate('category', array('categoryUrlWithFilters' => ltrim($url, '/')));
            }
        );

        $breadcrumbsBuilder->appendFilter($breadcrumbs, $filters);
        return $breadcrumbs;
    }

    /**
     * @param PriceFacet $price
     * @return string
     */
    protected function prepareFilterPriceValue(PriceFacet $price)
    {
        if ($price->getMin() && $price->getMax()) {
            return $price->getMin() . "-" . $price->getMax() . "zł";
        }

        if ($price->getMin()) {
            return "od " . $price->getMin() . "zł";
        }

        if ($price->getMax()) {
            return "do " . $price->getMax() . "zł";
        }
        return '-';
    }

    /**
     * @return array
     */
    protected function getProductFields()
    {
        $fieldsForList = ProductsRepository::$fieldsForList;
        $fieldsForList[] = '_categories.url_in';
        return $fieldsForList;
    }
}
