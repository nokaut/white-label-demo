<?php
/**
 * Created by PhpStorm.
 * User: jjuszkiewicz
 * Date: 23.09.2014
 * Time: 13:48
 */

namespace WL\AppBundle\Lib\Helper;


use Nokaut\ApiKit\Entity\Offer;
use Nokaut\ApiKit\Entity\Product;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ClickUrl
{
    const REDIRECT = 'redirect';
    const FRAME_OFFERS_SHOP = 'frame_offers_shop';
    const FRAME_OFFERS_CATEGORY = 'frame_offers_category';

    /**
     * @var string
     */
    protected $clickMode;
    /**
     * @var ContainerInterface
     */
    protected $container;

    function __construct(ContainerInterface $container)
    {
        $this->clickMode = $container->getParameter('click_mode');
        $this->container = $container;
    }


    /**
     * @param Offer $offer
     * @return string
     */
    public function prepareOfferClickUrl($offer)
    {
        if (self::REDIRECT == $this->clickMode) {
            return $this->generateUrl('clickRedirect', array('clickUrl' => urlencode($offer->getClickUrl())));
        }

        return $this->generateUrl('clickOffer', array('offerId' => $offer->getId()));

    }

    /**
     * @param Product $product
     * @return string
     */
    public function prepareProductClickUrl($product)
    {
        if (self::REDIRECT == $this->clickMode) {
            return $this->generateUrl('clickRedirect', array('clickUrl' => urlencode($product->getClickUrl())));
        }

        return $this->generateUrl('clickProduct', array('productId' => $product->getId()));

    }

    protected function generateUrl($route, $parameters = array())
    {
        return $this->container->get('router')->generate($route, $parameters, UrlGeneratorInterface::ABSOLUTE_PATH);
    }
} 