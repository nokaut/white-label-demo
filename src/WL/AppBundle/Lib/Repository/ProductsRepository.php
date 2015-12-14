<?php
/**
 * Created by PhpStorm.
 * User: jjuszkiewicz
 * Date: 10.10.2014
 * Time: 09:15
 */

namespace WL\AppBundle\Lib\Repository;


use Nokaut\ApiKit\ClientApi\Rest\Fetch\ProductsFetch;
use Nokaut\ApiKit\ClientApi\Rest\Query\Filter\MultipleWithOperator;
use Nokaut\ApiKit\ClientApi\Rest\Query\Filter\SingleWithOperator;
use Nokaut\ApiKit\ClientApi\Rest\Query\ProductsQuery;
use Nokaut\ApiKit\Collection\Products;

class ProductsRepository extends \Nokaut\ApiKit\Repository\ProductsRepository
{
    public static $fieldsForProductBox = array(
        'id', 'url', 'product_id', 'title', 'prices', 'offer_count', 'shop_count', 'category_id', 'offer_id',
        'url_original', 'offer_shop_id', 'shop_name', 'shop_url', 'top_category_id', 'top_position', 'photo_id',
        'click_url', 'click_value', 'shop', 'shop.url_logo', 'shop.name','description_short',
    );

    public static $fieldsForList = array(
        'id', 'product_id', 'title', 'prices', 'offer_count', 'url', 'shop', 'shop.url_logo', 'shop_count', 'category_id',
        'offer_id', 'click_url', 'click_value', 'url_original', 'producer_name', 'offer_shop_id', 'shop.name', 'shop_url',
        'shop_id', 'top_category_id', 'top_position', 'photo_id', 'description_short', 'properties', '_metadata.url',
        '_metadata.block_adsense', 'offer', 'block_adsense', '_metadata.urls', '_metadata.paging', '_metadata.sorts',
        '_metadata.canonical','_phrase.value', '_phrase.url_out', '_phrase.url_category_template', '_phrase.url_in_template'
    );

    /**
     * @param $productIdOffset
     * @param array $fields
     * @param array $categoriesIds
     * @param int $limit
     * @return \Nokaut\ApiKit\Collection\CollectionInterface|\Nokaut\ApiKit\Entity\EntityAbstract
     * @throws \Exception
     */
    public function fetchProductsWithIdOffset($productIdOffset, array $fields, array $categoriesIds = [], $limit = 200)
    {
        $query = new ProductsQuery($this->apiBaseUrl);
        if ($productIdOffset) {
            $query->addFilter(new SingleWithOperator('id', 'gte', $productIdOffset));
        }
        if (count($categoriesIds)) {
            $query->addFilter(new MultipleWithOperator('category_ids', 'in', $categoriesIds));
        }
        $query->setFields($fields);
        $query->setOrder('id', 'asc');
        $query->setLimit($limit);

        $fetch = new ProductsFetch($query, $this->cache);
        $this->clientApi->send($fetch);

        /**
         * nie ma operatora gt dla produktow - usuwamy pierwszy z wynikow
         */
        /** @var Products $products */
        $products = $fetch->getResult();
        $entities = $products->getEntities();
        array_shift($entities);
        $products->setEntities($entities);

        return $products;
    }
} 