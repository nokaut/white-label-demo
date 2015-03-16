<?php

namespace WL\AppBundle\Controller;

use Guzzle\Http\Client;
use Mobile_Detect;
use Nokaut\ApiKit\ClientApi\Rest\Fetch\OffersFetch;
use Nokaut\ApiKit\ClientApi\Rest\Fetch\ProductsFetch;
use Nokaut\ApiKit\ClientApi\Rest\Query\Filter\Single;
use Nokaut\ApiKit\ClientApi\Rest\Query\OffersQuery;
use Nokaut\ApiKit\ClientApi\Rest\Query\ProductsQuery;
use Nokaut\ApiKit\Entity\Offer;
use Nokaut\ApiKit\Repository\OffersAsyncRepository;
use Nokaut\ApiKit\Repository\OffersRepository;
use Nokaut\ApiKit\Repository\ProductsRepository;
use WL\AppBundle\Lib\Guzzle\Subscriber\UrlEncodeSubscriber;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use WL\AppBundle\Lib\Helper\ClickUrl;
use WL\AppBundle\Lib\Repository\ProductsAsyncRepository;

class ClickController extends Controller
{
    protected static $limitOffers = 30;
    protected static $miniOffersInIFrame = 3;

    public function clickProductAction($productId)
    {
        try {
            $offer = $this->fetchOfferFromProduct($productId);
            return $this->doIFrame($offer);

        } catch (\Exception $e) {
            throw $e;
//            return $this->redirect('/');
        }
    }

    public function clickOfferAction($offerId)
    {
        $offer = $this->fetchOffer($offerId);

        return $this->doIFrame($offer);
    }

    public function clickRedirectAction($clickUrl)
    {
        return $this->redirect($this->container->getParameter('click_domain') . urldecode($clickUrl));
    }

    /**
     * @param $id
     * @return Offer
     */
    protected function fetchOffer($id)
    {
        /** @var OffersRepository $offersRepository */
        $offersRepository = $this->get('repo.offers');
        return $offersRepository->fetchOfferById($id, OffersRepository::$fieldsAll);
    }

    /**
     * @param Offer $offer
     * @return ProductsFetch
     */
    protected function fetchProductsFromCategory($offer)
    {
        /** @var ProductsAsyncRepository $productsRepository */
        $productsRepository = $this->get('repo.products.async');

        $query = new ProductsQuery($this->container->getParameter('api_url'));
        $query->setFields(ProductsRepository::$fieldsWithBestOfferForProductBox);
        $query->setCategoryIds(array($offer->getCategoryId()));
        $query->setLimit(self::$limitOffers);
        return $productsRepository->fetchProductsWithBestOfferByQuery($query);
    }

    /**
     * @param Offer $offer
     * @return OffersFetch
     */
    protected function fetchOfferFromShop($offer)
    {
        /** @var OffersAsyncRepository $offersRepository */
        $offersRepository = $this->get('repo.offers.async');

        $query = new OffersQuery($this->container->getParameter('api_url'));
        $query->setFields(OffersRepository::$fieldsAll);
        $query->addFilter(new Single('shop_id', $offer->getShopId()));
        $query->addFilter(new Single('category_id', $offer->getCategoryId()));
        $query->setLimit(self::$limitOffers);

        return $offersRepository->fetchOffersByQuery($query);
    }

    /**
     * @param $productId
     * @throws \Exception
     * @return Offer
     */
    protected function fetchOfferFromProduct($productId)
    {
        /** @var OffersRepository $offersRepository */
        $offersRepository = $this->get('repo.offers');

        $offers = $offersRepository->fetchOffersByProductId($productId, OffersAsyncRepository::$fieldsAll);

        if ($offers && count($offers) > 0) {
            return $offers->getItem(0);
        }
        throw new \Exception('offer not found');
    }

    /**
     * @param Offer $offer
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function doIFrame($offer)
    {
        $clickMode = $this->container->getParameter('click_mode');
        $clickDomain = $this->container->getParameter('click_domain');
        $offers = $products = null;
        if ($clickMode == ClickUrl::FRAME_OFFERS_SHOP) {
            $offers = $this->fetchOfferFromShop($offer);
        } else {
            $products = $this->fetchProductsFromCategory($offer);
        }

        /** @var ProductsAsyncRepository $productsRepository */
        $productsRepository = $this->get('repo.products.async');
        $productsRepository->fetchAllAsync();

        $collection = $clickMode == ClickUrl::FRAME_OFFERS_SHOP ? $offers : $products;
        if ($this->iframeDisallowed($offer, $collection)) {
            return $this->redirect($this->container->getParameter('click_domain') . $offer->getClickUrl());
        }

        $iframeUrl = $clickDomain . $offer->getClickUrl();
        return $this->render('WLAppBundle:Click:click.html.twig', array(
            'iframeUrl' => $iframeUrl,
            'products' => $products ? $products->getResult() : null,
            'offers' => $offers ? $offers->getResult() : null,
            'offer' => $offer
        ));
    }

    /**
     * @param Offer $offer
     * @param OffersFetch $collection
     * @return mixed|string
     */
    protected function iframeDisallowed($offer, $collection)
    {
        $offersCount = $collection ? count($collection->getResult()) : 0;

        $detect = new Mobile_Detect();
        return (!$detect->isTablet() && $detect->isMobile())
        || $offersCount <= self::$miniOffersInIFrame
        || $this->isSslProtocol()
        || $this->isBrowserDisallowedIFrame()
        || $this->isShopDisallowedIFrame($offer);


    }

    /**
     * @param $shopId
     * @return mixed
     */
    protected function getCheckFromCache($shopId)
    {
        $session = new Session();
        $cacheKey = $this->getCacheKey($shopId);
        $cacheValue = $session->get($cacheKey);
        return $cacheValue !== null  ? (bool)$cacheValue : null;
    }

    /**
     * @param string $clickUrl
     * @param bool $value
     */
    protected function saveCheckToCache($clickUrl, $value)
    {
        $cacheKey = $this->getCacheKey($clickUrl);
        $session = new Session();
        $session->set($cacheKey, $value);
    }

    /**
     * @param $shopId
     * @return string
     */
    protected function getCacheKey($shopId)
    {
        return 'iframe-check-' . md5($shopId);
    }

    /**
     * @return bool
     */
    protected function isBrowserDisallowedIFrame()
    {
        $detect = new Mobile_Detect();

        // Safari is not allowed (cookies problem, security), we have to check that browser is not chrome
        if ($detect->version('Chrome', Mobile_Detect::VERSION_TYPE_FLOAT) === false
            and $detect->version('Safari', Mobile_Detect::VERSION_TYPE_FLOAT) > 0
        ) {
            return true;
        }

        return false;
    }

    protected function isSslProtocol()
    {
        $isSecure = false;
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
            $isSecure = true;
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' || !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on') {
            $isSecure = true;
        }
        return $isSecure;
    }

    /**
     * @param Offer $offer
     * @return bool|mixed
     */
    protected function isShopDisallowedIFrame($offer)
    {
        $checkFromCache = $this->getCheckFromCache($offer->getShop()->getId());
        if (null !== $checkFromCache) {
            return $checkFromCache;
        }
        $client = new Client('', array('request.options'=>array('timeout'=>5,'connect_timeout'=>2)));
        $client->addSubscriber(new UrlEncodeSubscriber());
        $request = $client->createRequest('GET', $offer->getUrl());
        $result = false;
        try {
            $response = $client->send($request);

            $headerXFrameOptions = $response->getHeader('X-Frame-Options');
            if ($headerXFrameOptions && in_array(strtoupper($headerXFrameOptions), array('SAMEORIGIN', 'DENY'))) {
                $result = true;
            }
        } catch (\Exception $e) {
            $result = true;
        }
        $this->saveCheckToCache($offer->getShop()->getId(), $result);
        return $result;
    }

}
