<?php

namespace WL\AppBundle\Lib\SiteMap;


use Monolog\Logger;
use Nokaut\ApiKit\Collection\Categories;
use Nokaut\ApiKit\Collection\Products;
use Nokaut\ApiKit\Entity\Category;
use Nokaut\ApiKit\Entity\Product;
use Nokaut\ApiKit\Repository\CategoriesRepository;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use WL\AppBundle\Lib\CategoriesAllowed;
use WL\AppBundle\Lib\Repository\ProductsRepository;

class SiteMapUrls
{
    /**
     * @var Router
     */
    private $router;

    /**
     * @var CategoriesRepository
     */
    private $categoriesRepository;

    /**
     * @var ProductsRepository
     */
    private $productsRepository;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var array
     */
    private $categoryFields = ['id', 'url', 'title', 'depth', 'parent_id', 'subcategory_count', 'total'];

    /**
     * @var Categories
     */
    private $categoriesTree;

    /**
     * @var CategoriesAllowed
     */
    private $categoriesAllowed;

    /**
     * @var string(24)
     */
    private $lastProductId;

    /**
     * SitemapUrls constructor.
     * @param Router $router
     * @param ProductsRepository $productsRepository
     * @param CategoriesRepository $categoriesRepository
     * @param CategoriesAllowed $categoriesAllowed
     * @param Logger $logger
     */
    public function __construct(Router $router, ProductsRepository $productsRepository, CategoriesRepository $categoriesRepository, CategoriesAllowed $categoriesAllowed, Logger $logger)
    {
        $this->router = $router;
        $this->categoriesAllowed = $categoriesAllowed;
        $this->productsRepository = $productsRepository;
        $this->categoriesRepository = $categoriesRepository;
        $this->logger = $logger;
        $this->categoriesTree = $this->fetchAllowedCategoriesTree();
    }

    /**
     * @return array
     */
    public function getProductsUrls()
    {
        $urls = [];
        while ($products = $this->getNextProductsPackage(200)) {
            if ($products) {
                /** @var Product $product */
                foreach ($products as $product) {
                    if ($product->getOfferCount() > 0) {
                        $urls[] = $this->router->generate('product', ['productUrl' => $product->getUrl()]);
                    }
                }
            }
            $memoryUsage = round(memory_get_usage(true) / 1024 / 1014, 2);
            $this->logger->info('products fetched: ' . count($products) . ', total ' . count($urls) .
                ', memory usage: ' . $memoryUsage . '/' . ini_get('memory_limit'));
        }

        return $urls;
    }

    /**
     * @param Categories $categories
     * @return array
     */
    public function getCategoriesUrls(Categories $categories = null)
    {
        if (!$categories) {
            $categories = $this->categoriesTree;
        }

        $urls = [];
        /** @var Category $category */
        foreach ($categories as $category) {
            if ($category->getTotal() > 10) {
                $urls[] = rtrim($this->router->generate('category', ['categoryUrlWithFilters' => trim($category->getUrl(), '/')]), '/') . '/';
                if (count($category->getChildren())) {
                    $urls = array_merge($urls, $this->getCategoriesUrls($category->getChildren()));
                }
            }
        }
        return $urls;
    }

    protected function fetchAllowedCategoriesTree()
    {
        $categories = $this->categoriesRepository->fetchCategoriesByIds($this->categoriesAllowed->getAllowedCategories(), 200, $this->categoryFields);

        foreach ($categories as $category) {
            /** @var Category $category */
            $children = $this->categoriesRepository->fetchByParentIdWithChildren($category->getId(), 2, $this->categoryFields);
            $category->setChildren($children);
            /** @var Category $child */
            foreach ($category->getChildren() as $child) {
                if ($child->getSubcategoryCount()) {
                    $tree = $this->fetchCategoriesTree($child->getId());
                    if (count($tree)) {
                        $child->setChildren($tree);
                    }
                }
            }
        }
        return $categories;
    }

    /**
     * @param int $parentId
     * @return \Nokaut\ApiKit\Collection\Categories
     */
    protected function fetchCategoriesTree($parentId = 0)
    {
        $categories = $this->categoriesRepository->fetchByParentIdWithChildren($parentId, 2, $this->categoryFields);

        /** @var Category $category */
        foreach ($categories as $category) {
            $this->logger->info('fetch category tree for ' . $category->getUrl());
            /** @var Category $child */
            foreach ($category->getChildren() as $child) {
                if ($child->getSubcategoryCount()) {
                    $tree = $this->fetchCategoriesTree($child->getId());
                    if (count($tree)) {
                        $child->setChildren($tree);
                    }
                }
            }
        }

        return $categories;
    }

    /**
     * Pobiera nastepna paczke produktow
     *
     * @param $limit
     *
     * @return array
     */
    protected function getNextProductsPackage($limit)
    {
        $lastProductId = $this->getLastProductId();

        $time = microtime(true);
        /** @var Products $receivedProducts */
        $receivedProducts = $this->getProducts($lastProductId, $limit);
        if (!count($receivedProducts)) {
            $this->logger->info('Products not found');
            return [];
        }

        $this->saveLastProductId($receivedProducts->getLast()->getId());
        $this->logger->info("lastProductId: " . $this->getLastProductId() . ' ' . round(microtime(true) - $time, 2) . 's');

        return $receivedProducts->getEntities();
    }

    /**
     * @param $lastProductId
     * @param $limit
     * @param int $attempt
     * @return \Nokaut\ApiKit\Collection\CollectionInterface|\Nokaut\ApiKit\Entity\EntityAbstract
     * @throws \Exception
     */
    protected function getProducts($lastProductId, $limit, $attempt = 1)
    {
        $categoriesIds = $this->categoriesAllowed->getAllowedCategories();
        try {
            return $this->productsRepository->fetchProductsWithIdOffset($lastProductId, ['id', 'url', 'offer_count'], $categoriesIds, $limit);
        } catch (\Exception $e) {
            if ($attempt > 60) {
                throw $e;
            }
            $sleepTime = $attempt * 3;

            $this->logger->error('Exception: ' . $e->getMessage() . ", retry after {$sleepTime}s sleep: " . $lastProductId);
            sleep($sleepTime);
            $attempt++;
            return $this->getProducts($lastProductId, $limit, $attempt);
        }
    }

    /**
     * Ustawia id ostatnio obrabianego produktu
     *
     * @param $lastProductId
     */
    protected function saveLastProductId($lastProductId)
    {
        $this->lastProductId = $lastProductId;
    }

    /**
     * Pobiera joinId ostatnio obrabianego produktu
     *
     * @return string(32)
     */
    protected function getLastProductId()
    {
        return $this->lastProductId;
    }
}