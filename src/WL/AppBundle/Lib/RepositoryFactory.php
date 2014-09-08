<?php
/**
 * Created by PhpStorm.
 * User: jjuszkiewicz
 * Date: 08.07.2014
 * Time: 18:57
 */

namespace WL\AppBundle\Lib;


use Nokaut\ApiKit\ApiKit;
use Nokaut\ApiKit\Config;
use Symfony\Component\DependencyInjection\ContainerInterface;
use WL\AppBundle\Repository\ProductsAsyncRepository;

class RepositoryFactory extends ApiKit
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $config = $this->prepareConfig($container);
        parent::__construct($config);
    }

    /**
     * @param \Nokaut\ApiKit\Config $config
     * @return ProductsAsyncRepository
     */
    public function getProductsAsyncRepository(Config $config = null)
    {
        if (is_null($config)) {
            $config = $this->config;
        }
        $this->validate($config);

        $restClientApi = $this->getClientApi($config);
        /** @var CategoriesAllowed $categoriesAllowed */
        $categoriesAllowed = $this->container->get('categories.allowed');
        return new ProductsAsyncRepository($config->getApiUrl(), $restClientApi, $categoriesAllowed);
    }

    /**
     * @return Config
     */
    protected function prepareConfig()
    {
        $config = new Config();
        $config->setApiAccessToken($this->container->getParameter('api_token'));
        $config->setApiUrl($this->container->getParameter('api_url'));
        $config->setCache($this->container->get('cache.memcache'));
        $config->setLogger($this->container->get('logger'));
        return $config;
    }
} 