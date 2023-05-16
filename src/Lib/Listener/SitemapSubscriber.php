<?php

namespace App\Lib\Listener;

use App\Lib\CategoriesAllowed;
use App\Lib\Repository\ProductsRepository;
use Exception;
use Nokaut\ApiKit\Collection\CollectionInterface;
use Nokaut\ApiKit\Collection\Products;
use Nokaut\ApiKit\Entity\Category;
use Nokaut\ApiKit\Entity\EntityAbstract;
use Nokaut\ApiKit\Repository\CategoriesRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Presta\SitemapBundle\Event\SitemapPopulateEvent;
use Presta\SitemapBundle\Service\UrlContainerInterface;
use Presta\SitemapBundle\Sitemap\Url\UrlConcrete;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class SitemapSubscriber implements EventSubscriberInterface
{
    /**
     * @var string(24)
     */
    private $lastProductId;

    private $categoriesTree;

    /**
     * @var array
     */
    private $categoryFields = ['id', 'url', 'title', 'depth', 'parent_id', 'subcategory_count', 'total'];

    private string $domain;

    public function __construct(
        private CategoriesAllowed     $categoriesAllowed,
        private CategoriesRepository  $categoriesRepository,
        private RouterInterface       $router,
        private LoggerInterface       $logger,
        private ProductsRepository    $productsRepository,
        private ParameterBagInterface $parameterBag
    )
    {
        $this->categoriesTree = $this->fetchAllowedCategoriesTree();
        $this->domain = $this->parameterBag->get('site_scheme') . '://' . $this->parameterBag->get('site_host');
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SitemapPopulateEvent::class => 'populate',
        ];
    }

    /**
     * @param SitemapPopulateEvent $event
     */
    public function populate(SitemapPopulateEvent $event): void
    {
        $this->registerCategoryUrls($event->getUrlContainer());
        $this->registerProductsUrls($event->getUrlContainer());
    }

    protected function fetchAllowedCategoriesTree()
    {
        $categories = $this->categoriesRepository->fetchCategoriesByIds($this->categoriesAllowed->getAllowedCategories(), 200, $this->categoryFields);

        foreach ($categories as $category) {
            /** @var Category $category */
            $children = $this->categoriesRepository->fetchByParentIdWithChildren($category->getId(), 2, $this->categoryFields);
            $category->setChildren($children);

            foreach ($category->getChildren() as $child) {
                if ($child->getSubcategoryCount()) {
                    $tree = $this->fetchCategoriesTree($child->getId());
                    if ($tree !== null) {
                        $child->setChildren($tree);
                    }
                }
            }
        }
        return $categories;
    }

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
                    if ($tree !== null) {
                        $child->setChildren($tree);
                    }
                }
            }
        }

        return $categories;
    }

    /**
     * @param UrlContainerInterface $urls
     */
    public function registerCategoryUrls(UrlContainerInterface $urls): void
    {
        $categories = $this->categoriesTree;

        foreach ($categories as $category) {
            $url = $this->domain . rtrim($this->router->generate('category', ['categoryUrlWithFilters' => trim($category->getUrl(), '/')]), '/') . '/';
            $urls->addUrl(new UrlConcrete($url), 'category');
        }
    }

    public function registerProductsUrls(UrlContainerInterface $urls)
    {
        $total = 0;
        while ($products = $this->getNextProductsPackage(200)) {
            dump("total:" . $total);
            foreach ($products as $product) {
                if ($product->getOfferCount() > 0) {
                    $url = $this->domain . $this->router->generate('product', ['productUrl' => ltrim($product->getUrl(), '/')]);
                    $urls->addUrl(new UrlConcrete($url), 'product');
                }
            }

            $total += count($products);
            $memoryUsage = round(memory_get_usage(true) / 1024 / 1014, 2);
            $this->logger->info('products fetched: ' . count($products) . ', total ' . $total .
                ', memory usage: ' . $memoryUsage . '/' . ini_get('memory_limit'));
        }
    }

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
     * Pobiera joinId ostatnio obrabianego produktu
     * @return string(32)
     */
    protected function getLastProductId()
    {
        return $this->lastProductId;
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

            $this->logger->error(printf("Exception: %s, retry after %s sleep: %s", $e->getMessage(), $sleepTime, $lastProductId));
            sleep($sleepTime);
            $attempt++;
            return $this->getProducts($lastProductId, $limit, $attempt);
        }
    }

    /**
     * Ustawia id ostatnio obrabianego produktu
     * @param $lastProductId
     */
    protected function saveLastProductId($lastProductId)
    {
        $this->lastProductId = $lastProductId;
    }
}