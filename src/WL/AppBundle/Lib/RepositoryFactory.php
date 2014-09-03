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
use WL\AppBundle\Lib\Cache\Memcache;

class RepositoryFactory
{

    /**
     * @param ContainerInterface $container
     * @return \Nokaut\ApiKit\Repository\ProductsRepository
     */
    public static function getProductsRepo(ContainerInterface $container)
    {
        $config = self::prepareConfig($container);

        $apiKit = new ApiKit($config);
        return $apiKit->getProductsRepository();
    }

    /**
     * @param ContainerInterface $container
     * @return \Nokaut\ApiKit\Repository\ProductsAsyncRepository
     */
    public static function getProductsAsyncRepo(ContainerInterface $container)
    {
        $config = self::prepareConfig($container);

        $apiKit = new ApiKit($config);
        return $apiKit->getProductsAsyncRepository();
    }

    /**
     * @param ContainerInterface $container
     * @return \Nokaut\ApiKit\Repository\CategoriesRepository
     */
    public static function getCategoriesRepo(ContainerInterface $container)
    {
        $config = self::prepareConfig($container);

        $apiKit = new ApiKit($config);
        return $apiKit->getCategoriesRepository();
    }

    /**
     * @param ContainerInterface $container
     * @return \Nokaut\ApiKit\Repository\CategoriesAsyncRepository
     */
    public static function getCategoriesAsyncRepo(ContainerInterface $container)
    {
        $config = self::prepareConfig($container);

        $apiKit = new ApiKit($config);
        return $apiKit->getCategoriesAsyncRepository();
    }

    /**
     * @param ContainerInterface $container
     * @return \Nokaut\ApiKit\Repository\OffersRepository
     */
    public static function getOffersRepo(ContainerInterface $container)
    {
        $config = self::prepareConfig($container);

        $apiKit = new ApiKit($config);
        return $apiKit->getOffersRepository();
    }

    /**
     * @param ContainerInterface $container
     * @return \Nokaut\ApiKit\Repository\OffersAsyncRepository
     */
    public static function getOffersAsyncRepo(ContainerInterface $container)
    {
        $config = self::prepareConfig($container);

        $apiKit = new ApiKit($config);
        return $apiKit->getOffersAsyncRepository();
    }

    /**
     * @param ContainerInterface $container
     * @return \Nokaut\ApiKit\Repository\AsyncRepository
     */
    public static function getAsyncRepo(ContainerInterface $container)
    {
        $config = self::prepareConfig($container);

        $apiKit = new ApiKit($config);
        return $apiKit->getAsyncRepository();
    }

    /**
     * @param ContainerInterface $container
     * @return Config
     */
    protected static function prepareConfig(ContainerInterface $container)
    {
        $config = new Config();
        $config->setApiAccessToken($container->getParameter('api_token'));
        $config->setApiUrl($container->getParameter('api_url'));
        $config->setCache($container->get('cache.memcache'));
        $config->setLogger($container->get('logger'));
        return $config;
    }
} 