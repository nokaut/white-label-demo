<?php
/**
 * Created by PhpStorm.
 * User: jjuszkiewicz
 * Date: 09.07.2014
 * Time: 23:30
 */

namespace WL\AppBundle\Controller;


use Nokaut\ApiKit\ClientApi\Rest\Exception\NotFoundException;
use Nokaut\ApiKit\ClientApi\Rest\Fetch\OffersFetch;
use Nokaut\ApiKit\ClientApi\Rest\Fetch\ProductsFetch;
use Nokaut\ApiKit\ClientApi\Rest\Query\ProductsQuery;
use Nokaut\ApiKit\Collection\Products;
use Nokaut\ApiKit\Entity\Category;
use Nokaut\ApiKit\Entity\Product;
use Nokaut\ApiKit\Repository\CategoriesAsyncRepository;
use Nokaut\ApiKit\Repository\OffersAsyncRepository;
use Nokaut\ApiKit\Repository\OffersRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WL\AppBundle\Lib\Breadcrumbs\BreadcrumbsBuilder;
use WL\AppBundle\Lib\CategoriesAllowed;
use WL\AppBundle\Lib\Exception\CategoryNotAllowedException;
use WL\AppBundle\Lib\Filter\PropertiesFilter;
use WL\AppBundle\Lib\Helper\Uri;
use WL\AppBundle\Lib\Filter;
use WL\AppBundle\Lib\Rating\RatingAdd;
use WL\AppBundle\Lib\Repository\ProductsRepository;
use WL\AppBundle\Lib\Type\Breadcrumb;
use WL\AppBundle\Lib\Repository\ProductsAsyncRepository;

class ProductController extends Controller
{
    public function indexAction($productUrl)
    {
        /** @var ProductsRepository $productsRepo */
        $productsRepo = $this->get('repo.products');
        try {
            $product = $productsRepo->fetchProductByUrl($productUrl, $this->getFieldsForProduct());
        } catch (NotFoundException $e) {
            return $this->redirect($this->generateUrl('search', array('phrase' => Uri::prepareApiUrl($productUrl))), 301);
        }
        $this->filter($product);

        /** @var CategoriesAsyncRepository $categoriesRepo */
        $categoriesRepo = $this->get('repo.categories.async');
        $categoryFetch = $categoriesRepo->fetchById($product->getCategoryId());

        /** @var OffersAsyncRepository $offersAsyncRepo */
        $offersRepo = $this->get('repo.offers.async');
        /** @var OffersFetch $offersFetch */
        $offersFetch = $offersRepo->fetchOffersByProductId($product->getId(), OffersRepository::$fieldsForProductPage);

        $productsFromCategoryFetch = $this->fetchProductsFromCategory($product->getCategoryId());
        $productsSimilarFetch = $this->fetchSimilarProducts($product);

        $categoriesRepo->fetchAllAsync();
        /** @var Category $category */
        $category = $categoryFetch->getResult();
        try {
            $this->checkAllowedCategory($category);
        } catch (CategoryNotAllowedException $e) {
            return $this->redirect($this->generateUrl('wl_homepage'), 301);
        }

        $this->filterCategory($category);
        $productsFromCategory = $productsFromCategoryFetch->getResult();
        $this->filterProducts($productsFromCategory);
        $productsSimilar = $productsSimilarFetch->getResult();
        $this->filterProducts($productsSimilar);

        $breadcrumbs = $this->prepareBreadcrumbs($category, $product);

        return $this->render('WLAppBundle:Product:index.html.twig', array(
            'product' => $product,
            'offers' => $offersFetch->getResult(),
            'productsTop10' => $productsFromCategory,
            'productsSimilar' => $productsSimilar,
            'breadcrumbs' => $breadcrumbs,
            'category' => $category,
            'canAddRating' => RatingAdd::canAddRate($product->getId())
        ));
    }

    public function addRateAction(Request $request)
    {
        $logger = $this->get('logger');
        try {
            $logger->info('add rating for product ' . $request->get('productId') . ", rating: " . $request->get('rating'));

            $rateAdd = new RatingAdd($this->container->getParameter('api_url'));
            $currentRating = $rateAdd->addRating($request->get('productId'), $request->get('rating'));

            return new Response($currentRating ? $currentRating : -1);
        } catch (\Exception $e) {
            $logger->error('Fail add rating for product ' . $request->get('productId') . ', '
                . $e->getMessage());
            return new Response('-1');
        }
    }

    /**
     * @param int $categoryId
     * @return ProductsFetch
     */
    protected function fetchProductsFromCategory($categoryId)
    {
        /** @var ProductsAsyncRepository $productsRepo */
        $productsRepo = $this->get('repo.products.async');
        $productsFetch = $productsRepo->fetchTopProducts(10, array($categoryId), 10);
        return $productsFetch;
    }

    protected function filter(Product $product)
    {
        $filter = new PropertiesFilter();
        $filter->filterProduct($product);
    }

    /**
     * @param Category $category
     */
    protected function filterCategory(Category $category)
    {
        $filterCategory = new Filter\UrlCategoryFilter();
        $filterCategory->filter($category);
    }

    /**
     * @param Products $products
     */
    protected function filterProducts(Products $products)
    {
        $filterUrl = new Filter\Controller\UrlCategoryFilter();
        $filterUrl->filter($products);
    }

    /**
     * @return array
     */
    protected function getFieldsForProduct()
    {
        return array('id', 'url', 'category_id', 'description_html', 'prices',
            'photo_id', 'producer_name', 'title', 'title_normalized',
            'properties', 'photo_ids', 'rating');
    }

    /**
     * @param $category
     * @param $product
     * @return array
     */
    protected function prepareBreadcrumbs($category, $product)
    {
        /** @var BreadcrumbsBuilder $breadcrumbsBuilder */
        $breadcrumbsBuilder = $this->get('breadcrumb.builder');
        $breadcrumbs = $breadcrumbsBuilder->prepareBreadcrumbs(
            $category,
            function ($url) {
                return $this->get('router')->generate('category', array('categoryUrlWithFilters' => $url));
            }
        );
        $breadcrumbs[] = new Breadcrumb($product->getTitle());
        return $breadcrumbs;
    }

    /**
     * @param Product $product
     * @return ProductsFetch
     */
    protected function fetchSimilarProducts($product)
    {
        $query = new ProductsQuery($this->container->getParameter('api_url'));
        $query->setFields(ProductsRepository::$fieldsForProductBox);
        $query->setLimit(10);
        $query->setCategoryIds(array($product->getCategoryId()));
        $query->setProducerName($product->getProducerName());
//        $query->setPhrase($product->getTitle());
//        $query->setQuality(60);
        /** @var ProductsAsyncRepository $productsRepo */
        $productsRepo = $this->get('repo.products.async');
        $productsFetch = $productsRepo->fetchProductsByQuery($query);
        return $productsFetch;
    }

    /**
     * @param $category
     * @throws CategoryNotAllowedException
     */
    protected function checkAllowedCategory($category)
    {
        /** @var CategoriesAllowed $categoryAllowed */
        $categoriesAllowed = $this->get('categories.allowed');
        $categoriesAllowed->checkAllowedCategory($category);
    }
}