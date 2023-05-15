<?php
/**
 * Created by PhpStorm.
 * User: jjuszkiewicz
 * Date: 09.07.2014
 * Time: 23:30
 */

namespace App\Controller;


use App\Lib\Breadcrumbs\BreadcrumbsBuilder;
use App\Lib\CategoriesAllowed;
use App\Lib\Exception\CategoryNotAllowedException;
use App\Lib\Filter\Controller\UrlCategoryFilter;
use App\Lib\Filter\PropertiesFilter;
use App\Lib\Helper\Uri;
use App\Lib\Rating\RatingAdd;
use App\Lib\Repository\ProductsAsyncRepository;
use App\Lib\Repository\ProductsRepository;
use App\Lib\Type\Breadcrumb;
use App\Lib\Filter;
use Exception;
use Nokaut\ApiKit\ClientApi\Rest\Exception\NotFoundException;
use Nokaut\ApiKit\ClientApi\Rest\Fetch\ProductsFetch;
use Nokaut\ApiKit\ClientApi\Rest\Query\ProductsQuery;
use Nokaut\ApiKit\Collection\Products;
use Nokaut\ApiKit\Entity\Category;
use Nokaut\ApiKit\Entity\Product;
use Nokaut\ApiKit\Repository\CategoriesAsyncRepository;
use Nokaut\ApiKit\Repository\OffersAsyncRepository;
use Nokaut\ApiKit\Repository\OffersRepository;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

class ProductController extends AbstractController
{
    public function __construct(
        private ProductsRepository        $productsRepository,
        private CategoriesAsyncRepository $categoriesAsyncRepository,
        private OffersAsyncRepository     $offersAsyncRepository,
        private ProductsAsyncRepository   $productsAsyncRepository,
        private ParameterBagInterface     $parameterBag,
        private CategoriesAllowed         $categoriesAllowed,
        private BreadcrumbsBuilder        $breadcrumbsBuilder,
        private RouterInterface           $router,
        private LoggerInterface           $logger
    )
    {
    }

    public function indexAction($productUrl)
    {
        if ($this->getParameter('product_mode') == 'modal') {
            throw $this->createNotFoundException('modal product mode - disallowed products page');
        }

        try {
            $product = $this->productsRepository->fetchProductByUrl($productUrl, $this->getFieldsForProduct());
        } catch (NotFoundException $e) {
            return $this->redirect($this->generateUrl('search', array('phrase' => Uri::prepareApiUrl($productUrl))), 301);
        }
        $this->filter($product);


        $categoryFetch = $this->categoriesAsyncRepository->fetchById($product->getCategoryId());


        $offersFetch = $this->offersAsyncRepository->fetchOffersByProductId($product->getId(), OffersRepository::$fieldsForProductPage);

        $productsFromCategoryFetch = $this->fetchProductsFromCategory($product->getCategoryId());
        $productsSimilarFetch = $this->fetchSimilarProducts($product);

        $this->categoriesAsyncRepository->fetchAllAsync();
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

        return $this->render('Product/index.html.twig', array(
            'product' => $product,
            'offers' => $offersFetch->getResult(),
            'productsTop10' => $productsFromCategory,
            'productsSimilar' => $productsSimilar,
            'breadcrumbs' => $breadcrumbs,
            'category' => $category,
            'canAddRating' => RatingAdd::canAddRate($product->getId())
        ));
    }

    public function modalAction($productUrl)
    {
        /** @var ProductsRepository $productsRepo */
        $productsRepo = $this->productsRepository;

        try {
            $product = $productsRepo->fetchProductByUrl($productUrl, $this->getFieldsForProduct());
        } catch (NotFoundException $e) {
            throw $e;
//            return $this->render('WLAppBundle:Product:modalNonProduct.html.twig'); todo: no existing file
        }
        $this->filter($product);


        $categoryFetch = $this->categoriesAsyncRepository->fetchById($product->getCategoryId());

        $productsFromCategoryFetch = $this->fetchProductsFromCategory($product->getCategoryId());
        $productsSimilarFetch = $this->fetchSimilarProducts($product);

        $offersFetch = $this->offersAsyncRepository->fetchOffersByProductId($product->getId(), OffersRepository::$fieldsForProductPage);

        $this->categoriesAsyncRepository->fetchAllAsync();
        /** @var Category $category */
        $category = $categoryFetch->getResult();

        $this->filterCategory($category);
        $productsFromCategory = $productsFromCategoryFetch->getResult();
        $this->filterProducts($productsFromCategory);
        $productsSimilar = $productsSimilarFetch->getResult();
        $this->filterProducts($productsSimilar);

        return $this->render('Product/modal.html.twig', [
            'product' => $product,
            'offers' => $offersFetch->getResult(),
            'productsTop10' => $productsFromCategory,
            'productsSimilar' => $productsSimilar,
            'category' => $category,
            'canAddRating' => RatingAdd::canAddRate($product->getId())
        ]);
    }

    public function addRateAction(Request $request)
    {
        try {
            $this->logger->info('add rating for product ' . $request->get('productId') . ", rating: " . $request->get('rating'));

            $rateAdd = new RatingAdd($this->get('repo.products'));
            $currentRating = $rateAdd->addRating($request->get('productId'), $request->get('rating'));

            return new Response($currentRating ? $currentRating->getRating() : -1);
        } catch (Exception $e) {
            $this->logger->error('Fail add rating for product ' . $request->get('productId') . ', '
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
        $productsFetch = $this->productsAsyncRepository->fetchTopProducts(10, array($categoryId));
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
        $filterUrl = new UrlCategoryFilter();
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

        $breadcrumbs = $this->breadcrumbsBuilder->prepareBreadcrumbs(
            $category,
            function ($url) {
                return $this->router->generate('category', array('categoryUrlWithFilters' => $url));
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
        $query = new ProductsQuery($this->parameterBag->get('api_url'));
        $query->setFields(ProductsRepository::$fieldsForProductBox);
        $query->setLimit(10);
        $query->setCategoryIds(array($product->getCategoryId()));
        $query->setProducerName($product->getProducerName());
//        $query->setPhrase($product->getTitle());
//        $query->setQuality(60);
        /** @var ProductsAsyncRepository $productsRepo */

        $productsFetch = $this->productsAsyncRepository->fetchProductsByQuery($query);
        return $productsFetch;
    }

    /**
     * @param $category
     * @throws CategoryNotAllowedException
     */
    protected function checkAllowedCategory($category)
    {
        if($category) {
            $this->categoriesAllowed->checkAllowedCategory($category);
        }
    }
}