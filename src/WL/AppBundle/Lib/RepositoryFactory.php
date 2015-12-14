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
use WL\AppBundle\Lib\Repository\ProductsAsyncRepository;
use WL\AppBundle\Lib\Repository\ProductsRepository;

class RepositoryFactory extends ApiKit
{
    /**
     * @var CategoriesAllowed
     */
    protected $categoriesAllowed;

    public function __construct(Config $config, CategoriesAllowed $categoriesAllowed)
    {
        $this->categoriesAllowed = $categoriesAllowed;
        parent::__construct($config);
    }

    /**
     * @param Config $config
     * @return ProductsRepository
     */
    public function getProductsRepository(Config $config = null)
    {
        if (!$config) {
            $config = $this->config;
        }
        $this->validate($config);

        $restClientApi = $this->getClientApi($config);

        return new ProductsRepository($config, $restClientApi);
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
        return new ProductsAsyncRepository($config, $restClientApi, $this->categoriesAllowed);
    }
} 