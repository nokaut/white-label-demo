<?php
/**
 * Created by PhpStorm.
 * User: jjuszkiewicz
 * Date: 02.10.2014
 * Time: 10:30
 */

namespace WL\AppBundle\Lib\Filter;


use Nokaut\ApiKit\Collection\Products;
use WL\AppBundle\Lib\Helper\UrlSearch;

class UrlFilter
{
    /**
     * @var UrlSearch
     */
    protected $urlSearch;

    function __construct(UrlSearch $urlSearch)
    {
        $this->urlSearch = $urlSearch;
    }


    public function filter(Products $products)
    {
        $this->filterSorts($products);
        $this->filterPrices($products);
        $this->filterProducers($products);
        $this->filterShops($products);
        $this->filterProperties($products);
        $this->filterPhrase($products);
        $this->filterPagination($products);
        $this->filterUrl($products);
        $this->filterCanonical($products);
    }

    /**
     * @param Products $products
     */
    protected function filterSorts(Products $products)
    {
        if (!$products->getMetadata()->getSorts()) {
            $products->getMetadata()->setSorts(array());
            return;
        }


        foreach ($products->getMetadata()->getSorts() as $sort) {
            $reducedUrl = $this->urlSearch->getReduceUrl($sort->getUrl());
            $sort->setUrl($reducedUrl);
        }
    }

    protected function filterPrices(Products $products)
    {
        if (!$products->getPrices()) {
            $products->setPrices(array());
            return;
        }


        foreach ($products->getPrices() as $price) {
            $reducedUrl = $this->urlSearch->getReduceUrl($price->getUrl());
            $price->setUrl($reducedUrl);
        }
    }

    protected function filterProducers(Products $products)
    {
        if (!$products->getProducers()) {
            $products->setProducers(array());
            return;
        }

        foreach ($products->getProducers() as $producer) {
            $reducedUrl = $this->urlSearch->getReduceUrl($producer->getUrl());
            $producer->setUrl($reducedUrl);
        }
    }

    protected function filterShops(Products $products)
    {
        if (!$products->getShops()) {
            $products->setShops(array());
            return;
        }

        foreach ($products->getShops() as $shop) {
            $reducedUrl = $this->urlSearch->getReduceUrl($shop->getUrl());
            $shop->setUrl($reducedUrl);
        }
    }

    protected function filterProperties(Products $products)
    {
        if (!$products->getProperties()) {
            $products->setProperties(array());
            return;
        }

        foreach ($products->getProperties() as $property) {
            foreach ($property->getValues() as $value) {
                $reducedUrl = $this->urlSearch->getReduceUrl($value->getUrl());
                $value->setUrl($reducedUrl);
            }
        }
    }

    /**
     * @param Products $products
     */
    protected function filterPhrase(Products $products)
    {
        if (!$products->getPhrase()) {
            return;
        }

        $reducedUrl = $this->urlSearch->getReduceUrl($products->getPhrase()->getUrlCategoryTemplate());
        $products->getPhrase()->setUrlCategoryTemplate($reducedUrl);

        $reducedUrl = $this->urlSearch->getReduceUrl($products->getPhrase()->getUrlInTemplate());
        $products->getPhrase()->setUrlInTemplate($reducedUrl);

        $reducedUrl = $this->urlSearch->getReduceUrl($products->getPhrase()->getUrlOut());
        $products->getPhrase()->setUrlOut($reducedUrl);
    }

    /**
     * @param Products $products
     */
    protected function filterPagination(Products $products)
    {
        if (!$products->getMetadata() || !$products->getMetadata()->getPaging()) {
            return;
        }

        $reducedUrl = $this->urlSearch->getReduceUrl($products->getMetadata()->getPaging()->getUrlTemplate());
        $products->getMetadata()->getPaging()->setUrlTemplate($reducedUrl);
    }

    /**
     * @param Products $products
     */
    protected function filterUrl(Products $products)
    {
        if (!$products->getMetadata()) {
            return;
        }
        $reducedUrl = $this->urlSearch->getReduceUrl($products->getMetadata()->getUrl());
        $products->getMetadata()->setUrl($reducedUrl);
    }

    /**
     * @param Products $products
     */
    protected function filterCanonical(Products $products)
    {
        if (!$products->getMetadata()) {
            return;
        }
        $reducedUrl = $this->urlSearch->getReduceUrl($products->getMetadata()->getCanonical());
        $products->getMetadata()->setCanonical($reducedUrl);
    }

} 