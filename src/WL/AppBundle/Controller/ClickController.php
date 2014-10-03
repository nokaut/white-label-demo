<?php

namespace WL\AppBundle\Controller;

use Nokaut\ApiKit\ClientApi\Rest\Fetch\OffersFetch;
use Nokaut\ApiKit\ClientApi\Rest\Fetch\ProductsFetch;
use Nokaut\ApiKit\ClientApi\Rest\Query\Filter\Single;
use Nokaut\ApiKit\ClientApi\Rest\Query\OffersQuery;
use Nokaut\ApiKit\ClientApi\Rest\Query\ProductsQuery;
use Nokaut\ApiKit\Entity\Offer;
use Nokaut\ApiKit\Repository\OffersAsyncRepository;
use Nokaut\ApiKit\Repository\OffersRepository;
use Nokaut\ApiKit\Repository\ProductsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use WL\AppBundle\Lib\Helper\ClickUrl;
use WL\AppBundle\Lib\Repository\ProductsAsyncRepository;

class ClickController extends Controller
{
    protected static $limitOffers = 30;

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
        return $this->redirect($this->container->getParameter('click_domain') .urldecode($clickUrl));
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
        $offers = $products = null;
        if ($clickMode == ClickUrl::FRAME_OFFERS_SHOP) {
            $offers = $this->fetchOfferFromShop($offer);
        } else {
            $products = $this->fetchProductsFromCategory($offer);
        }

        /** @var ProductsAsyncRepository $productsRepository */
        $productsRepository = $this->get('repo.products.async');
        $productsRepository->fetchAllAsync();


        $iframeUrl = 'http://www.nokaut.pl' . $offer->getClickUrl();
        return $this->render('WLAppBundle:Click:click.html.twig', array(
            'iframeUrl' => $iframeUrl,
            'products' => $products ? $products->getResult() : null,
            'offers' => $offers ? $offers->getResult() : null,
            'offer' => $offer
        ));
    }

}
