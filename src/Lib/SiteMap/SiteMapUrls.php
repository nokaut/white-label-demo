<?php

namespace App\Lib\SiteMap;


use App\Lib\CategoriesAllowed;
use App\Lib\Repository\ProductsRepository;
use Exception;
use Monolog\Logger;
use Nokaut\ApiKit\Collection\Categories;
use Nokaut\ApiKit\Collection\CollectionInterface;
use Nokaut\ApiKit\Collection\Products;
use Nokaut\ApiKit\Entity\Category;
use Nokaut\ApiKit\Entity\EntityAbstract;
use Nokaut\ApiKit\Entity\Product;
use Nokaut\ApiKit\Repository\CategoriesRepository;
use SitemapPHP\Sitemap;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

class SiteMapUrls
{
    /**
     * @var string
     */
    private $domain;

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
     * @var Sitemap $siteMap
     */
    private $siteMap;

    /**
     * SitemapUrls constructor.
     * @param string $domain
     * @param Router $router
     * @param ProductsRepository $productsRepository
     * @param CategoriesRepository $categoriesRepository
     * @param CategoriesAllowed $categoriesAllowed
     * @param Logger $logger
     */
    public function __construct($domain, Router $router, ProductsRepository $productsRepository, CategoriesRepository $categoriesRepository, CategoriesAllowed $categoriesAllowed, Logger $logger)
    {
        $this->domain = $domain;
        $this->siteMap = new Sitemap($domain);
        $this->router = $router;
        $this->categoriesAllowed = $categoriesAllowed;
        $this->productsRepository = $productsRepository;
        $this->categoriesRepository = $categoriesRepository;
        $this->logger = $logger;
        $this->categoriesTree = $this->fetchAllowedCategoriesTree();
    }

    public function createSiteMap($siteMapTarget)
    {
        $this->logger->info('START: xml sitemap create');


        $this->createTargetDir($siteMapTarget);
        $this->siteMap->setPath($siteMapTarget);

        $this->logger->info('dump categories...');
        $this->setCategoriesUrls();
        $this->logger->info('dump products...');
        $this->setProductsUrls();


        $this->siteMap->createSitemapIndex($this->domain, 'Today');

        $this->logger->info('STOP: xml sitemap create');
    }

    protected function setProductsUrls()
    {
        $total = 0;
        while ($products = $this->getNextProductsPackage(200)) {
            if ($products) {
                /** @var Product $product */
                foreach ($products as $product) {
                    if ($product->getOfferCount() > 0) {
                        $url = $this->router->generate('product', ['productUrl' => ltrim($product->getUrl(), '/')]);
                        $this->siteMap->addItem(ltrim($url, '/'));
                    }
                }
            }
            $total += count($products);
            $memoryUsage = round(memory_get_usage(true) / 1024 / 1014, 2);
            $this->logger->info('products fetched: ' . count($products) . ', total ' . $total .
                ', memory usage: ' . $memoryUsage . '/' . ini_get('memory_limit'));
        }
    }

    /**
     * @param Categories $categories
     * @return array
     */
    public function setCategoriesUrls(Categories $categories = null)
    {
        if (!$categories) {
            $categories = $this->categoriesTree;
        }

        /** @var Category $category */
        foreach ($categories as $category) {
            if ($category->getTotal() > 10) {
                $url = rtrim($this->router->generate('category', ['categoryUrlWithFilters' => trim($category->getUrl(), '/')]), '/') . '/';
                $this->siteMap->addItem(ltrim($url, '/'));
                if (count($category->getChildren())) {
                    $this->setCategoriesUrls($category->getChildren());
                }
            }
        }
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
     * @return Categories
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
     * @return CollectionInterface|EntityAbstract
     * @throws Exception
     */
    protected function getProducts($lastProductId, $limit, $attempt = 1)
    {
        $categoriesIds = $this->categoriesAllowed->getAllowedCategories();
        try {
            return $this->productsRepository->fetchProductsWithIdOffset($lastProductId, ['id', 'url', 'offer_count'], $categoriesIds, $limit);
        } catch (Exception $e) {
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

    private function createTargetDir($targetDir)
    {
        $targetDir = rtrim($targetDir, '/') . '/';
        $this->logger->info('Create sitemap dir: ' . $targetDir);
        if (!file_exists($targetDir)) {
            mkdir($targetDir);
        }
    }
}