<?php

namespace WL\AppBundle\Lib\Filter\Controller;


use Nokaut\ApiKit\Collection\Products;
use Nokaut\ApiKit\Entity\Metadata\Facet\CategoryFacet;
use Nokaut\ApiKit\Entity\Product;
use WL\AppBundle\Lib\Helper\Uri;

class UrlCategoryFilter
{
    public function filter(Products $products)
    {
        $this->filterProduct($products);
        $this->filterCategories($products);
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
    protected function filterProduct(Products $products)
    {
        /** @var Product $product */
        foreach ($products as $product) {
            $url = Uri::prepareApiUrl($product->getUrl());
            $product->setUrl($url);
        }
    }

    /**
     * @param Products $products
     */
    protected function filterCategories(Products $products)
    {
        /** @var CategoryFacet $category */
        foreach ($products->getCategories() as $category) {
            $reducedUrl = Uri::prepareApiUrl($category->getUrl());
            $category->setUrl($reducedUrl);
            $reducedUrl = Uri::prepareApiUrl($category->getUrlIn());
            $category->setUrlIn($reducedUrl);
            $reducedUrl = Uri::prepareApiUrl($category->getUrlOut());
            $category->setUrlOut($reducedUrl);
            $reducedUrl = Uri::prepareApiUrl($category->getUrlBase());
            $category->setUrlBase($reducedUrl);
        }
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
            $reducedUrl = Uri::prepareApiUrl($sort->getUrl());
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
            $reducedUrl = Uri::prepareApiUrl($price->getUrl());
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
            $reducedUrl = Uri::prepareApiUrl($producer->getUrl());
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
            $reducedUrl = Uri::prepareApiUrl($shop->getUrl());
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
                $reducedUrl = Uri::prepareApiUrl($value->getUrl());
                $value->setUrl($reducedUrl);
            }

            if ($property->getRanges()) {
                foreach ($property->getRanges() as $value) {
                    $reducedUrl = Uri::prepareApiUrl($value->getUrl());
                    $value->setUrl($reducedUrl);
                }
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

        $reducedUrl = Uri::prepareApiUrl($products->getPhrase()->getUrlCategoryTemplate());
        $products->getPhrase()->setUrlCategoryTemplate($reducedUrl);

        $reducedUrl = Uri::prepareApiUrl($products->getPhrase()->getUrlInTemplate());
        $products->getPhrase()->setUrlInTemplate($reducedUrl);

        $reducedUrl = Uri::prepareApiUrl($products->getPhrase()->getUrlOut());
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

        $reducedUrl = Uri::prepareApiUrl($products->getMetadata()->getPaging()->getUrlTemplate());
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
        $reducedUrl = Uri::prepareApiUrl($products->getMetadata()->getUrl());
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
        $reducedUrl = Uri::prepareApiUrl($products->getMetadata()->getCanonical());
        $products->getMetadata()->setCanonical($reducedUrl);
    }

} 