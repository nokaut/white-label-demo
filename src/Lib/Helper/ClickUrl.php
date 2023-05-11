<?php
/**
 * Created by PhpStorm.
 * User: jjuszkiewicz
 * Date: 23.09.2014
 * Time: 13:48
 */

namespace App\Lib\Helper;


use Nokaut\ApiKit\Entity\Offer;
use Nokaut\ApiKit\Entity\Product;
use Nokaut\ApiKit\Entity\Product\OfferWithBestPrice;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


class ClickUrl
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param Offer|OfferWithBestPrice $offer
     * @return string
     */
    public function prepareOfferClickUrl($offer)
    {
        return $this->generateUrl('clickRedirect', array('clickUrl' => urlencode($offer->getClickUrl())));

    }

    /**
     * @param Product $product
     * @return string
     */
    public function prepareProductClickUrl($product)
    {
        return $this->generateUrl('clickRedirect', array('clickUrl' => urlencode($product->getClickUrl())));
    }

    protected function generateUrl($route, $parameters = array())
    {
        return $this->container->get('router')->generate($route, $parameters, UrlGeneratorInterface::ABSOLUTE_PATH);
    }
} 