<?php
/**
 * Created by PhpStorm.
 * User: jjuszkiewicz
 * Date: 08.07.2014
 * Time: 18:57
 */

namespace App\Lib;


use App\Lib\Repository\ProductsAsyncRepository;
use App\Lib\Repository\ProductsRepository;
use Nokaut\ApiKit\ApiKit;
use Nokaut\ApiKit\Config;

class RepositoryFactory extends ApiKit
{
    public function __construct(Config $config, private CategoriesAllowed $categoriesAllowed)
    {
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