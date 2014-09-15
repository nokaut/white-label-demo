<?php
/**
 * Created by PhpStorm.
 * User: jjuszkiewicz
 * Date: 09.07.2014
 * Time: 23:30
 */

namespace WL\AppBundle\Controller;


use Nokaut\ApiKit\ClientApi\Rest\Async\OffersAsyncFetch;
use Nokaut\ApiKit\ClientApi\Rest\Async\ProductsAsyncFetch;
use Nokaut\ApiKit\Entity\Category;
use Nokaut\ApiKit\Entity\Product;
use Nokaut\ApiKit\Repository\CategoriesAsyncRepository;
use Nokaut\ApiKit\Repository\OffersRepository;
use Nokaut\ApiKit\Repository\ProductsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WL\AppBundle\Lib\BreadcrumbsBuilder;
use WL\AppBundle\Lib\Filter\FilterProperties;
use WL\AppBundle\Lib\Rating\RatingAdd;
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
        } catch (\Exception $e) {
            throw $this->createNotFoundException("not found product: " . $productUrl);
        }
        $this->removeUselessProperties($product);

        /** @var CategoriesAsyncRepository $categoriesRepo */
        $categoriesRepo = $this->get('repo.categories.async');
        $categoryFetch = $categoriesRepo->fetchById($product->getCategoryId());

        /** @var OffersRepository $offersAsyncRepo */
        $offersRepo = $this->get('repo.offers.async');
        /** @var OffersAsyncFetch $offersFetch */
        $offersFetch = $offersRepo->fetchOffersByProductId($product->getId(), OffersRepository::$fieldsForProductPage);

        $productsFromCategoryFetch = $this->fetchProductsFromCategory($product->getCategoryId());

        $categoriesRepo->fetchAllAsync();
        /** @var Category $category */
        $category = $categoryFetch->getResult();

        $breadcrumbs = $this->prepareBreadcrumbs($category, $product);

        return $this->render('WLAppBundle:Product:index.html.twig', array(
            'product' => $product,
            'offers' => $offersFetch->getResult(),
            'productsTop10' => $productsFromCategoryFetch->getResult(),
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
     * @return ProductsAsyncFetch
     */
    protected function fetchProductsFromCategory($categoryId)
    {
        /** @var ProductsAsyncRepository $productsRepo */
        $productsRepo = $this->get('repo.products.async');
        $productsFetch = $productsRepo->fetchTopProducts(10, array($categoryId), 10);
        return $productsFetch;
    }

    private function removeUselessProperties(Product $product)
    {
        $filter = new FilterProperties();
        $filteredProperties = $filter->filterProperties($product->getProperties());
        $product->setProperties($filteredProperties);
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
    private function prepareBreadcrumbs($category, $product)
    {
        /** @var BreadcrumbsBuilder $breadcrumbsBuilder */
        $breadcrumbsBuilder = $this->get('breadcrumb.builder');
        $breadcrumbs = $breadcrumbsBuilder->prepareBreadcrumbs(
            $category,
            function ($url) {
                return $this->get('router')->generate('category', array('categoryUrlWithFilters' => ltrim($url, '/')));
            }
        );
        $breadcrumbs[] = new Breadcrumb($product->getTitle());
        return $breadcrumbs;
    }
}