<?php

namespace WL\AppBundle\Controller;

use Nokaut\ApiKit\Collection\Products;
use Nokaut\ApiKit\Entity\Category;
use Nokaut\ApiKit\Entity\Product;
use Symfony\Component\HttpFoundation\Response;
use WL\AppBundle\Lib\BreadcrumbsBuilder;
use WL\AppBundle\Lib\Filter\PropertiesFilter;
use WL\AppBundle\Lib\Filter\UrlFilter;
use WL\AppBundle\Lib\Helper\UrlSearch;
use WL\AppBundle\Lib\Pagination\Pagination;
use WL\AppBundle\Lib\Type\Breadcrumb;
use WL\AppBundle\Lib\Repository\ProductsAsyncRepository;
use WL\AppBundle\Lib\View\Data\Converter\Filters\Callback;
use Nokaut\ApiKit\Ext\Data;
use WL\AppBundle\Lib\View\Data\Converter\Filters\Callback\Categories\ReduceAllSelected;

class SearchController extends CategoryController
{
    public function indexAction($phrase)
    {
        /** @var UrlSearch $urlSearchPreparer */
        $urlSearchPreparer = $this->get('helper.url_search');
        $phraseUrlForApi = $urlSearchPreparer->preparePhraseWithAllowCategories($phrase);

        /** @var ProductsAsyncRepository $productsRepo */
        $productsRepo = $this->get('repo.products.async');
        $productsFetch = $productsRepo->fetchProductsByUrl($phraseUrlForApi, $this->getProductFields(), 24);
        $productsRepo->fetchAllAsync();
        /** @var Products $products */
        $products = $productsFetch->getResult();

        $this->filter($products);

        $pagination = $this->preparePagination($products);

        $priceFilters = $this->getPriceFilters($products);
        $producersFilters = $this->getProducersFilters($products);
        $propertiesFilters = $this->getPropertiesFilters($products);
        $categoriesFilters = $this->getCategoriesFiltersForSearch($products);

        $selectedFilters = $this->getSelectedFilters($products);
        $selectedCategoriesFilters = $this->getCategoriesSelectedFilters($products);

        $breadcrumbs = $this->prepareBreadcrumbs($products, $selectedFilters);

        $phrase = $products ? $products->getMetadata()->getQuery()->getPhrase() : '';

        $responseStatus = null;
        if ($products->getMetadata()->getTotal() == 0) {
            return $this->render('WLAppBundle:Category:nonResult.html.twig', array(
                'phrase' => $phrase,
                'breadcrumbs' => $breadcrumbs,
                'selectedFilters' => $selectedFilters,
                'canonical' => $products ? $products->getMetadata()->getCanonical() : '',
            ), new Response('', 404));
        }

        return $this->render('WLAppBundle:Search:index.html.twig', array(
            'products' => $products,
            'phrase' => $phrase,
            'breadcrumbs' => $breadcrumbs,
            'pagination' => $pagination,
            'subcategories' => $categoriesFilters,
            'priceFilters' => $priceFilters,
            'producersFilters' => $producersFilters,
            'propertiesFilters' => $propertiesFilters,
            'selectedFilters' => $selectedFilters,
            'sorts' => $products ? $products->getMetadata()->getSorts() : array(),
            'canonical' => $products ? $products->getMetadata()->getCanonical() : '',
            'h1' => $this->prepareH1($selectedCategoriesFilters),
            'metadataTitle' => $this->prepareSearchMetadataTitle($phrase, $pagination)
        ), $responseStatus);
    }

    /**
     * filtering products and facets etc...
     * @param Products $products
     */
    protected function filter($products)
    {
        if ($products === null) {
            return;
        }
        $filterUrl = new UrlFilter($this->get('helper.url_search'));
        $filterUrl->filter($products);

        parent::filter($products);
    }

    /**
     * @param Products|null $products
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
        $pagination->setUrlTemplate(
            $this->get('router')->generate('search', array('phrase' => ltrim($products->getMetadata()->getPaging()->getUrlTemplate(), '/')))
        );
        return $pagination;
    }

    /**
     * @param Products $products
     * @param Data\Collection\Filters\FiltersAbstract[] $filters
     * @return Breadcrumb[]
     */
    protected function prepareBreadcrumbs($products, array $filters)
    {
        $breadcrumbs = array();
        $breadcrumbs[] = new Breadcrumb("Szukasz: " . $products->getMetadata()->getQuery()->getPhrase());
        /** @var BreadcrumbsBuilder $breadcrumbsBuilder */
        $breadcrumbsBuilder = $this->get('breadcrumb.builder');
        $breadcrumbsBuilder->appendFilter($breadcrumbs, $filters);
        return $breadcrumbs;
    }

    protected function getCategoriesSelectedFilters($products)
    {
        $converterFilter = new Data\Converter\Filters\Selected\CategoriesConverter();
        $categoriesFilter = $converterFilter->convert($products, array(
            new ReduceAllSelected(),
        ));
        return $categoriesFilter;
    }

    /**
     * @param Products $products
     * @return Data\Collection\Filters\Categories
     */
    protected function getCategoriesFiltersForSearch($products)
    {
        $converterFilter = new Data\Converter\Filters\CategoriesConverter();
        $categoriesFilter = $converterFilter->convert($products,array(
            new Data\Converter\Filters\Callback\Categories\SetIsExcluded(),
            new Data\Converter\Filters\Callback\Categories\SortByName(),
        ));
        return $categoriesFilter;
    }

    /**
     * @param Data\Collection\Filters\Categories $selectedCategoriesFilters
     * @return string
     */
    protected function prepareH1($selectedCategoriesFilters)
    {
        $result = '';
        foreach ($selectedCategoriesFilters as $entity) {
            $result .= $entity->getName() . ', ';
        }
        return trim($result, ', ');
    }

    /**
     * @param $phrase
     * @param Pagination $pagination
     * @return string
     */
    protected function prepareSearchMetadataTitle($phrase, $pagination)
    {
        $title = $phrase;

        if ($pagination->getCurrentPage() > 1) {
            $title .= " (str. " . $pagination->getCurrentPage() . ")";
        }
        return $title;
    }

}
