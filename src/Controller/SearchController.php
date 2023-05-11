<?php

namespace App\Controller;

use App\Lib\Breadcrumbs\BreadcrumbsBuilder;
use App\Lib\Helper\UrlSearch;
use App\Lib\Pagination\Pagination;
use App\Lib\Repository\ProductsAsyncRepository;
use App\Lib\Repository\ProductsRepository;
use App\Lib\Type\Breadcrumb;
use App\Lib\View\Data\Converter\Filters\Callback\Categories\ReduceAllSelected;
use App\Lib\Filter;
use App\Lib\View\Data\Converter\Filters\Callback;
use Nokaut\ApiKit\Collection\Products;
use Nokaut\ApiKit\Ext\Data;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class SearchController extends AbstractController
{
    public function __construct(
        private UrlSearch $urlSearch,
        private ProductsAsyncRepository $productsAsyncRepository,
        private BreadcrumbsBuilder $breadcrumbsBuilder
    )
    {
    }

    public function indexAction($phrase)
    {
        $phraseUrlForApi = $this->urlSearch->preparePhrase($phrase);

        $productsFetch = $this->productsAsyncRepository->fetchProductsByUrlWithQuality($phraseUrlForApi, $this->getProductFields(), 24, 60);
        $this->productsAsyncRepository->fetchAllAsync();
        /** @var Products $products */
        $products = $productsFetch->getResult();

        $this->filter($products);

        $pagination = $this->preparePagination($products);

        $priceFilters = $this->getPriceFilters($products);
        $producersFilters = $this->getProducersFilters($products);
        $propertiesFilters = $this->getPropertiesFilters($products);
        $categoriesFilters = $this->getCategoriesFilters($products);

        $selectedFilters = $this->getSelectedFilters($products);
        $selectedCategoriesFilters = $this->getCategoriesSelectedFilters($products);

        $breadcrumbs = $this->prepareBreadcrumbs($products, $selectedFilters, $selectedCategoriesFilters);

        $phrase = $products ? $products->getMetadata()->getQuery()->getPhrase() : '';

        $responseStatus = null;
        if ($products->getMetadata()->getTotal() == 0) {
            return $this->render('Category/nonResult.html.twig', array(
                'phrase' => $phrase,
                'breadcrumbs' => $breadcrumbs,
                'selectedFilters' => $selectedFilters,
                'canonical' => $this->getCanonical($products),
            ), new Response('', 410));
        }

        return $this->render('Search/index.html.twig', array(
            'products' => $products,
            'phrase' => $phrase,
            'breadcrumbs' => $breadcrumbs,
            'pagination' => $pagination,
            'subcategories' => $categoriesFilters,
            'priceFilters' => $priceFilters,
            'producersFilters' => $producersFilters,
            'propertiesFilters' => $propertiesFilters,
            'selectedFilters' => $selectedFilters,
            'selectedCategoriesFilters' => $selectedCategoriesFilters,
            'sorts' => $products ? $products->getMetadata()->getSorts() : array(),
            'canonical' => $this->getCanonical($products),
            'h1' => $this->prepareH1($selectedCategoriesFilters),
            'metadataTitle' => $this->prepareSearchMetadataTitle($phrase, $pagination, $selectedCategoriesFilters)
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
        $filterUrl = new \App\Lib\Filter\Controller\UrlSearchFilter($this->urlSearch);
        $filterUrl->filter($products);

        $filterProperties = new \App\Lib\Filter\PropertiesFilter();
        $filterProperties->filterProducts($products);

        $filterSort = new \App\Lib\Filter\SortFilter();
        $filterSort->filter($products);
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
            $this->get('router')->generate('search', ['phrase' => $products->getMetadata()->getPaging()->getUrlTemplate()])
        );
        return $pagination;
    }

    /**
     * @param Products $products
     * @param Data\Collection\Filters\FiltersAbstract[] $filters
     * @param Data\Collection\Filters\Categories $selectedCategories
     * @return Breadcrumb[]
     */
    protected function prepareBreadcrumbs($products, array $filters, $selectedCategories)
    {
        $breadcrumbs = array();
        if (count($selectedCategories)) {
            foreach ($selectedCategories as $selectedCategory) {
                /** @var Data\Entity\Filter\Category $selectedCategory */
                $breadcrumbs[] = new Breadcrumb(
                    $selectedCategory->getName(),
                    $this->generateUrl('category', array('categoryUrlWithFilters' => $selectedCategory->getUrlBase()))
                );
            }
        }
        if ($products->getMetadata()->getQuery()->getPhrase()) {
            $breadcrumbs[] = new Breadcrumb("Szukasz: " . $products->getMetadata()->getQuery()->getPhrase());
        }
        $this->breadcrumbsBuilder->appendFilter($breadcrumbs, $filters);
        return $breadcrumbs;
    }

    /**
     * @param Products $products
     * @return Data\Collection\Filters\PriceRanges
     */
    protected function getPriceFilters($products)
    {
        $converterFilter = new Data\Converter\Filters\PriceRangesConverter();
        $priceRangesSelectedFilter = $converterFilter->convert($products, array(
            new Data\Converter\Filters\Callback\PriceRanges\SetIsNofollow(),
        ));
        return $priceRangesSelectedFilter;
    }

    /**
     * @param $products
     * @return Data\Collection\Filters\Producers
     */
    protected function getProducersFilters($products)
    {
        $converterFilter = new Data\Converter\Filters\ProducersConverter();
        $producersSelectedFilter = $converterFilter->convert($products, array(
            new Data\Converter\Filters\Callback\Producers\SetIsNofollow(),
            new Data\Converter\Filters\Callback\Producers\SetIsPopular(),
            new Data\Converter\Filters\Callback\Producers\SetIsActive(),
            new Data\Converter\Filters\Callback\Producers\SortByName(),
        ));
        return $producersSelectedFilter;
    }

    /**
     * @param Products $products
     * @return Data\Collection\Filters\PropertyAbstract[]
     */
    protected function getPropertiesFilters($products)
    {
        $converterFilter = new Data\Converter\Filters\PropertiesConverter();
        $propertiesFilter = $converterFilter->convert($products,array(
            new Data\Converter\Filters\Callback\Property\SetIsActive(),
            new Data\Converter\Filters\Callback\Property\SetIsExcluded(),
            new Data\Converter\Filters\Callback\Property\SetIsNofollow(),
            new Data\Converter\Filters\Callback\Property\SortDefault(),
        ));
        return $propertiesFilter;
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
    protected function getCategoriesFilters($products)
    {
        $converterFilter = new Data\Converter\Filters\CategoriesConverter();
        $categoriesFilter = $converterFilter->convert($products,array(
            new \App\Lib\View\Data\Converter\Filters\Callback\Categories\ReduceIncorrectCategories(),
            new Data\Converter\Filters\Callback\Categories\SetIsExcluded(),
            new Data\Converter\Filters\Callback\Categories\SortByName(),
        ));
        return $categoriesFilter;
    }

    /**
     * @param Products $products
     * @return Data\Collection\Filters\FiltersAbstract[]
     */
    protected function getSelectedFilters($products)
    {
        if (is_null($products)) {
            return array();
        }

        $selectedFilters = array();

        $priceSelectedFilters = $this->getPriceSelectedFilters($products);
        if ($priceSelectedFilters->count()) {
            $selectedFilters[] = $priceSelectedFilters;
        }

        $producersSelectedFilters = $this->getProducersSelectedFilters($products);
        if ($producersSelectedFilters->count()) {
            $selectedFilters[] = $producersSelectedFilters;
        }

        $propertiesSelectedFilters = $this->getPropertiesSelectedFilter($products);
        $selectedFilters = array_merge($selectedFilters, $propertiesSelectedFilters);

        return $selectedFilters;
    }

    /**
     * @param Products $products
     * @return Data\Collection\Filters\PriceRanges
     */
    protected function getPriceSelectedFilters($products)
    {
        $converterSelectedFilter = new Data\Converter\Filters\Selected\PriceRangesConverter();
        $priceRangesSelectedFilter = $converterSelectedFilter->convert($products, array(
            new Data\Converter\Filters\Callback\PriceRanges\SetIsNofollow(),
            new \App\Lib\View\Data\Converter\Filters\Callback\PriceRanges\SetName()
        ));
        return $priceRangesSelectedFilter;
    }

    /**
     * @param $products
     * @return Data\Collection\Filters\Producers
     */
    protected function getProducersSelectedFilters($products)
    {
        $converterSelectedFilter = new Data\Converter\Filters\Selected\ProducersConverter();
        $producersSelectedFilter = $converterSelectedFilter->convert($products, array(
            new Data\Converter\Filters\Callback\Producers\SetIsNofollow(),
        ));
        return $producersSelectedFilter;
    }

    /**
     * @param Products $products
     * @return Data\Collection\Filters\PropertyAbstract[]
     */
    protected function getPropertiesSelectedFilter($products)
    {
        $converterSelectedFilter = new Data\Converter\Filters\Selected\PropertiesConverter();
        $propertiesFilter = $converterSelectedFilter->convert($products,array(
            new Data\Converter\Filters\Callback\Property\SetIsNofollow(),
        ));
        return $propertiesFilter;
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
     * @param Data\Collection\Filters\Categories $selectedCategoriesFilters
     * @return string
     */
    protected function prepareSearchMetadataTitle($phrase, $pagination, $selectedCategoriesFilters)
    {
        if ($phrase) {
            $title = $phrase;
        } else {
            $title = $this->prepareH1($selectedCategoriesFilters);
        }

        if ($pagination->getCurrentPage() > 1) {
            $title .= " (str. " . $pagination->getCurrentPage() . ")";
        }
        return $title;
    }

    /**
     * @return array
     */
    protected function getProductFields()
    {
        $fieldsForList = ProductsRepository::$fieldsForList;
        $fieldsForList[] = '_categories.url_in';
        $fieldsForList[] = '_categories.url_base';
        return $fieldsForList;
    }

    /**
     * @param Products $products
     * @return string
     */
    protected function getCanonical($products)
    {
        return $products ? $products->getMetadata()->getCanonical() : '';
    }

}
