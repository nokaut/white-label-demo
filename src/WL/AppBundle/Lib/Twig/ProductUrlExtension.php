<?php
/**
 * Created by PhpStorm.
 * User: jjuszkiewicz
 * Date: 09.09.2014
 * Time: 16:45
 */

namespace WL\AppBundle\Lib\Twig;

use Nokaut\ApiKit\Entity\Product;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use WL\AppBundle\Lib\Helper\ClickUrl;

class ProductUrlExtension extends \Twig_Extension
{
    /**
     * @var boolean
     */
    protected $productModal;
    /**
     * @var ClickUrl
     */
    protected $clickUrl;
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * ProductUrlExtension constructor.
     * @param ContainerInterface $container
     * @param ClickUrl $clickUrl
     */
    public function __construct(ContainerInterface $container, $clickUrl)
    {
        $this->productModal = $container->getParameter('product_mode');
        $this->clickUrl = $clickUrl;
        $this->container = $container;
    }

    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('productUrlAttr', [$this, 'prepareAttrUrl'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * @param Product $product
     * @param bool $oneOfferToShop
     * @return string
     */
    function prepareAttrUrl($product, $oneOfferToShop = true)
    {
        $attr = [];
        if ($this->productModal == 'modal' && !$product->getClickUrl()) {
            $attr['href'] = '#' . ltrim($product->getUrl(), '/');
            $attr['data-product-modal'] = ltrim($product->getUrl(), '/');
        } elseif ($this->productModal == 'modal' && $product->getClickUrl()) {
            $attr['target'] = "_blank";
            $attr['href'] = $this->clickUrl->prepareProductClickUrl($product);
            $attr['rel'] = 'nofollow';
        } elseif ($oneOfferToShop && $product->getClickUrl()) {
            $attr['target'] = "_blank";
            $attr['href'] = $this->clickUrl->prepareProductClickUrl($product);
            $attr['rel'] = 'nofollow';
        } else {
            $attr['href'] = $this->container->get('router')->generate('product', ['productUrl' => $product->getUrl()], UrlGeneratorInterface::ABSOLUTE_PATH);
        }

        $result = '';
        foreach ($attr as $attrName => $value) {
            $result .= "{$attrName}=\"{$value}\" ";
        }
        return trim($result);
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'productUrlAttr';
    }
}
