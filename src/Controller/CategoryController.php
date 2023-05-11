<?php

namespace App\Controller;

use App\Lib\Breadcrumbs\BreadcrumbsBuilder;
use App\Lib\CategoriesAllowed;
use App\Lib\Exception\CategoryNotAllowedException;
use App\Lib\Filter\PropertiesFilter;
use App\Lib\Filter\SortFilter;
use App\Lib\Filter\UrlCategoryFilter;
use App\Lib\Helper\Uri;
use App\Lib\Pagination\Pagination;
use App\Lib\Repository\ProductsAsyncRepository;
use App\Lib\Repository\ProductsRepository;
use App\Lib\Type\Breadcrumb;
use App\Lib\Filter;
use App\Lib\View\Data\Converter\Filters\Callback;
use App\Lib\View\Data\Converter\Filters\Callback\Categories\ReduceIncorrectCategories;
use App\Lib\View\Data\Converter\Filters\Callback\Categories\SetParentCategory;
use App\Lib\View\Data\Converter\Filters\Callback\PriceRanges\SetName;
use Nokaut\ApiKit\ClientApi\Rest\Exception\NotFoundException;
use Nokaut\ApiKit\Collection\Products;
use Nokaut\ApiKit\Entity\Category;
use Nokaut\ApiKit\Ext\Data;
use Nokaut\ApiKit\Repository\CategoriesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CategoryController extends AbstractController
{
    public function __construct(
        private ProductsAsyncRepository $productsAsyncRepository,
        private CategoriesRepository    $categoriesRepository,
        private CategoriesAllowed       $categoriesAllowed,
        private BreadcrumbsBuilder      $breadcrumbsBuilderService,
    )
    {
    }

    public function indexAction($categoryUrlWithFilters)
    {
        try {
            $category = $this->fetchCategory($categoryUrlWithFilters);
        } catch (CategoryNotAllowedException $e) {
            return $this->redirect($this->generateUrl('wl_homepage'), 301);
        }

        $productsFetch = $this->productsAsyncRepository->fetchProductsByUrl($categoryUrlWithFilters, $this->getProductFields(), 24);
        $this->productsAsyncRepository->fetchAllAsync();

        /** @var Products $products */
        $products = $productsFetch->getResult();

        $this->filter($products);
        $this->filterCategory($category);
        $pagination = $this->preparePagination($products);

        $priceFilters = $this->getPriceFilters($products);
        $producersFilters = $this->getProducersFilters($products);
        $propertiesFilters = $this->getPropertiesFilters($products);
        $categoriesFilters = $this->getCategoriesFilters($category, $products);

        $selectedFilters = $this->getSelectedFilters($products);

        $breadcrumbs = $this->prepareBreadcrumbs($category, $selectedFilters);

        $responseStatus = null;
        if ($products->getMetadata()->getTotal() == 0) {
            return $this->render('Category/nonResult.html.twig', array(
                'breadcrumbs' => $breadcrumbs,
                'selectedFilters' => $selectedFilters,
                'canonical' => $this->getCanonical($products),
            ), new Response('', 410));
        }

        return $this->render('Category/index.html.twig', [
            'category' => $category,
            'products' => $products,
            'breadcrumbs' => $breadcrumbs,
            'pagination' => $pagination,
            'subcategories' => $categoriesFilters,
            'priceFilters' => $priceFilters,
            'producersFilters' => $producersFilters,
            'propertiesFilters' => $propertiesFilters,
            'selectedFilters' => $selectedFilters,
            'sorts' => $products ? $products->getMetadata()->getSorts() : array(),
            'canonical' => $this->getCanonical($products),
            'h1' => $category->getTitle(),
            'metadataTitle' => $this->prepareMetadataTitle($breadcrumbs, $selectedFilters, $pagination)
        ]);
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

        $filterUrl = new Filter\Controller\UrlCategoryFilter();
        $filterUrl->filter($products);

        $filterProperties = new PropertiesFilter();
        $filterProperties->filterProducts($products);

        $filterSort = new SortFilter();
        $filterSort->filter($products);
    }

    /**
     * @param Category $category
     */
    protected function filterCategory(Category $category)
    {
        $filterCategory = new UrlCategoryFilter();
        $filterCategory->filter($category);
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
            $this->generateUrl('category', ['categoryUrlWithFilters' => $products->getMetadata()->getPaging()->getUrlTemplate()], UrlGeneratorInterface::ABSOLUTE_URL)
        );
        return $pagination;
    }

    /**
     * @param $categoryUrlWithFilters
     * @return Category
     * @throws NotFoundHttpException
     * @throws CategoryNotAllowedException
     */
    public function fetchCategory($categoryUrlWithFilters)
    {
        $path = explode('/', $categoryUrlWithFilters);
        $categoryUrl = $path[0];
        try {
            $category = $this->categoriesRepository->fetchByUrl($categoryUrl);

            if ($category) {
                $this->categoriesAllowed->checkAllowedCategory($category);
            }

            return $category;
        } catch (NotFoundException $e) {
            throw $this->createNotFoundException("not found category " . $categoryUrl);
        }
    }

    /**
     * @param $category
     * @param Data\Collection\Filters\FiltersAbstract[] $filters
     * @return array
     */
    protected function prepareBreadcrumbs($category, array $filters)
    {
        $breadcrumbs = $this->breadcrumbsBuilderService->prepareBreadcrumbs(
            $category,
            function ($url) {
                return $this->generateUrl('category', array('categoryUrlWithFilters' => Uri::prepareApiUrl($url)));
            }
        );

        $this->breadcrumbsBuilderService->appendFilter($breadcrumbs, $filters);
        return $breadcrumbs;
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
     * @return array
     */
    protected function getProductFields()
    {
        $fieldsForList = ProductsRepository::$fieldsForList;
        $fieldsForList[] = '_categories.url_in';
        return $fieldsForList;
    }

    /**
     * @param Products $products
     * @return Data\Collection\Filters\PriceRanges
     */
    protected function getPriceSelectedFilters($products)
    {

        $converterSelectedFilter = new Data\Converter\Filters\Selected\PriceRangesConverter();
        $priceRangesSelectedFilter = $converterSelectedFilter->convert($products, [
            new Data\Converter\Filters\Callback\PriceRanges\SetIsNofollow(),
            new SetName()
        ]);
        return $priceRangesSelectedFilter;
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
    protected function getProducersSelectedFilters($products)
    {
        $converterSelectedFilter = new Data\Converter\Filters\Selected\ProducersConverter();
        $producersSelectedFilter = $converterSelectedFilter->convert($products, array(
            new Data\Converter\Filters\Callback\Producers\SetIsNofollow(),
        ));
        return $producersSelectedFilter;
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
    protected function getPropertiesSelectedFilter($products)
    {
        $converterSelectedFilter = new Data\Converter\Filters\Selected\PropertiesConverter();
        $propertiesFilter = $converterSelectedFilter->convert($products, array(
            new Data\Converter\Filters\Callback\Property\SetIsNofollow(),
        ));
        return $propertiesFilter;
    }

    /**
     * @param Products $products
     * @return Data\Collection\Filters\PropertyAbstract[]
     */
    protected function getPropertiesFilters($products)
    {
        $converterFilter = new Data\Converter\Filters\PropertiesConverter();
        $propertiesFilter = $converterFilter->convert($products, array(
            new Data\Converter\Filters\Callback\Property\SetIsActive(),
            new Data\Converter\Filters\Callback\Property\SetIsExcluded(),
            new Data\Converter\Filters\Callback\Property\SetIsNofollow(),
            new Data\Converter\Filters\Callback\Property\SortDefault(),
        ));
        return $propertiesFilter;
    }

    /**
     * @param Category $category
     * @param Products $products
     * @return Data\Collection\Filters\Categories
     */
    protected function getCategoriesFilters($category, $products)
    {
        $converterFilter = new Data\Converter\Filters\CategoriesConverter();
        $categoriesFilter = $converterFilter->convert($products, array(
            new ReduceIncorrectCategories(),
            new Data\Converter\Filters\Callback\Categories\SetIsExcluded(),
            new Data\Converter\Filters\Callback\Categories\SortByName(),
            new SetParentCategory($category, $this->categoriesAllowed)
        ));
        return $categoriesFilter;
    }

    /**
     * @param Breadcrumb[] $breadcrumbs
     * @param array $selectedFilters
     * @param Pagination $pagination
     * @return string
     */
    protected function prepareMetadataTitle($breadcrumbs, $selectedFilters, $pagination)
    {
        $title = "";
        if (count($breadcrumbs)) {
            if ($selectedFilters && count($breadcrumbs) > 1) {
                $title .= $breadcrumbs[count($breadcrumbs) - 2]->getTitle();
            }
            $title .= " " . $breadcrumbs[count($breadcrumbs) - 1]->getTitle();
        }

        if ($pagination->getCurrentPage() > 1) {
            $title .= " (str. " . $pagination->getCurrentPage() . ")";
        }
        return $title;
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
