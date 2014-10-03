<?php

namespace WL\AppBundle\Controller;

use Nokaut\ApiKit\Collection\Products;
use Nokaut\ApiKit\Entity\Category;
use Nokaut\ApiKit\Entity\Metadata\Facet\PriceFacet;
use Nokaut\ApiKit\Entity\Product;
use Nokaut\ApiKit\Repository\ProductsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use WL\AppBundle\Lib\BreadcrumbsBuilder;
use WL\AppBundle\Lib\Filter\PropertiesFilter;
use WL\AppBundle\Lib\Helper\UrlSearch;
use WL\AppBundle\Lib\Pagination\Pagination;
use WL\AppBundle\Lib\Type\Breadcrumb;
use WL\AppBundle\Lib\Type\Filter;
use WL\AppBundle\Lib\Repository\ProductsAsyncRepository;
use WL\AppBundle\Lib\View\Data\Collection\Filters\PropertyAbstract;
use WL\AppBundle\Lib\View\Data\Converter\Filters\Facets\PropertiesConverter as FacetsPropertiesConverter;
use WL\AppBundle\Lib\Filter\UrlFilter;

class SearchController extends Controller
{
    public function indexAction($phrase)
    {
        /** @var UrlSearch $urlSearchPreparer */
        $urlSearchPreparer = $this->get('helper.url_search');
        $phraseUrlForApi = $urlSearchPreparer->preparePhraseWithAllowCategories($phrase);

        /** @var ProductsAsyncRepository $productsRepo */
        $productsRepo = $this->get('repo.products.async');
        $productsFetch = $productsRepo->fetchProductsByUrl($phraseUrlForApi, $this->getProductFields(), 24);
        $productsTopFetch = $productsRepo->fetchTopProducts();
        $productsRepo->fetchAllAsync();
        /** @var Products $products */
        $products = $productsFetch->getResult();

        $this->filter($products);

        $pagination = $this->preparePagination($products);

        $filterPropertiesFacets = $this->getFilterPropertiesFacet($products);

        $filters = $this->getFilters($products, $urlSearchPreparer);

        $breadcrumbs = $this->prepareBreadcrumbs($products, $filters);

        $responseStatus = null;
        if ($products->getMetadata()->getTotal() == 0) {
            $responseStatus = new Response('', 404);
        }

        return $this->render('WLAppBundle:Search:index.html.twig', array(
            'products' => $products,
            'phrase' => $products ? $products->getMetadata()->getQuery()->getPhrase() : '',
            'breadcrumbs' => $breadcrumbs,
            'subcategories' => $products ? $products->getCategories() : array(),
            'filters' => $filters,
            'filterPropertiesFacets' => $filterPropertiesFacets,
            'sorts' => $products->getMetadata()->getSorts(),
            'pagination' => $pagination,
            'canonical' => $products ? $products->getMetadata()->getCanonical() : '',
            'productsTop10' => $productsTopFetch->getResult()
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

        $filterProperties = new PropertiesFilter();
        $filterProperties->filterProducts($products);
    }

    /**
     * @param $products
     * @return PropertyAbstract[]
     */
    protected function getFilterPropertiesFacet($products)
    {
        $facetsPropertiesConverter = new FacetsPropertiesConverter();
        $filterPropertiesFacets = $facetsPropertiesConverter->convert($products);
        return $filterPropertiesFacets;
    }

    /**
     * @param Products $products
     * @param UrlSearch $urlSearchPreparer
     * @return Filter[]
     */
    protected function getFilters($products, UrlSearch $urlSearchPreparer)
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
                $filter->setOutUrl($urlSearchPreparer->getReduceUrl($producer->getUrl()));
                $filters[] = $filter;
            }
        }
        foreach ($products->getPrices() as $price) {
            if ($price->getIsFilter()) {
                $filter = new Filter();
                $filter->setName("Ceny");
                $filter->setValue($this->prepareFilterPriceValue($price));
                $filter->setOutUrl($urlSearchPreparer->getReduceUrl($price->getUrl()));
                $filters[] = $filter;
            }
        }
        foreach ($products->getProperties() as $property) {
            foreach ($property->getValues() as $value) {
                if ($value->getIsFilter()) {
                    $filter = new Filter();
                    $filter->setName($property->getName());
                    $filter->setValue($value->getName());
                    $filter->setOutUrl($urlSearchPreparer->getReduceUrl($value->getUrl()));
                    $filters[] = $filter;
                }
            }
        }
        return $filters;
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
     * @param $filters
     * @return Breadcrumb[]
     */
    protected function prepareBreadcrumbs($products, $filters)
    {
        $breadcrumbs = array();
        $breadcrumbs[] = new Breadcrumb("Szukaj: " . $products->getMetadata()->getQuery()->getPhrase());
        /** @var BreadcrumbsBuilder $breadcrumbsBuilder */
        $breadcrumbsBuilder = $this->get('breadcrumb.builder');
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
            return "od " . $price->getMin() . " do " . $price->getMax() . "zł";
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
