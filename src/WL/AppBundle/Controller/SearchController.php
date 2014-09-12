<?php

namespace WL\AppBundle\Controller;

use Nokaut\ApiKit\ClientApi\Rest\Async\ProductsAsyncFetch;
use Nokaut\ApiKit\Collection\Products;
use Nokaut\ApiKit\Entity\Category;
use Nokaut\ApiKit\Entity\Product;
use Nokaut\ApiKit\Repository\CategoriesRepository;
use Nokaut\ApiKit\Repository\ProductsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use WL\AppBundle\Lib\BreadcrumbsBuilder;
use WL\AppBundle\Lib\Filter\FilterProperties;
use WL\AppBundle\Lib\Pagination\Pagination;
use WL\AppBundle\Lib\Type\Breadcrumb;
use WL\AppBundle\Lib\Type\Filter;
use WL\AppBundle\Lib\Repository\ProductsAsyncRepository;

class SearchController extends Controller
{
    public function indexAction($phrase)
    {
        $phraseUrl = $this->preparePhraseUrl($phrase);
        $phraseUrlForApi = $this->reduceToAllowCategories($phraseUrl);

        /** @var ProductsAsyncRepository $productsRepo */
        $productsRepo = $this->get('repo.products.async');
        $productsFetch = $productsRepo->fetchProductsByUrl($phraseUrlForApi, ProductsRepository::$fieldsForList, 24);
        $productsTopFetch = $productsRepo->fetchTopProducts();
        $productsRepo->fetchAllAsync();
        /** @var Products $products */
        $products = $productsFetch->getResult();
        $pagination = $this->preparePagination($products);

        $filters = $this->getFilters($products);
        $this->setCategoryToProduct($products);

        $breadcrumbs = $this->prepareBreadcrumbs($products, $filters);

        return $this->render('WLAppBundle:Search:index.html.twig', array(
            'products' => $this->filterProducts($productsFetch),
            'phrase' => $products ? $products->getMetadata()->getQuery()->getPhrase() : '',
            'breadcrumbs' => $breadcrumbs,
            'subcategories' => $products ? $products->getCategories() : array(),
            'filters' => $filters,
            'sorts' => $products ? $products->getMetadata()->getSorts() : array(),
            'pagination' => $pagination,
            'url' => $phraseUrl,
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
                $filter->setName("Ceny");
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
    private function preparePhraseUrl($phrase)
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

    /**
     * add categories to url for filter only for allowed categories
     * @param string $phrase
     * @return string
     */
    protected function reduceToAllowCategories($phrase)
    {
        if ($this->hasCategory($phrase)) {
            return $phrase;
        }
        /** @var CategoriesRepository $categoriesRepository */
        $categoriesRepository = $this->get('repo.categories.cache.file');
        $categories = $categoriesRepository->fetchCategoriesByIds($this->get('categories.allowed')->getAllowedCategories());

        $categoriesUrlPart = "";

        foreach ($categories as $category) {
            /** @var Category $category */
            $categoriesUrlPart .= trim($category->getUrl(), '/') . ",";
        }
        return rtrim($categoriesUrlPart, ',') . '/' . $phrase;
    }

    /**
     * @param string $phrase
     * @return bool
     */
    protected function hasCategory($phrase)
    {
        $phraseParts = explode('/', ltrim($phrase, '/'));

        return count($phraseParts) > 1;
    }

    /**
     * @param Products $products
     * @param $filters
     * @return Breadcrumb[]
     */
    private function prepareBreadcrumbs($products, $filters)
    {
        $breadcrumbs = array();
        $breadcrumbs[] = new Breadcrumb("Szukaj: " . $products->getMetadata()->getQuery()->getPhrase());
        /** @var BreadcrumbsBuilder $breadcrumbsBuilder */
        $breadcrumbsBuilder = $this->get('breadcrumb.builder');
        $breadcrumbsBuilder->appendFilter($breadcrumbs, $filters);
        return $breadcrumbs;
    }

}
