<?php
/**
 * Created by PhpStorm.
 * User: jjuszkiewicz
 * Date: 23.09.2014
 * Time: 14:22
 */

namespace WL\AppBundle\Lib\Twig;


use Nokaut\ApiKit\Entity\EntityAbstract;
use Nokaut\ApiKit\Entity\Offer;
use Nokaut\ApiKit\Entity\Product;
use WL\AppBundle\Lib\Helper\ClickUrl;

class ClickUrlExtension extends \Twig_Extension
{
    /**
     * @var ClickUrl
     */
    protected $clickUrl;

    function __construct($clickUrl)
    {
        $this->clickUrl = $clickUrl;
    }


    public function getFilters()
    {
        return array(
            'click' => new \Twig_Filter_Method($this, 'clickUrl'),
        );
    }

    /**
     * @param EntityAbstract $entity
     * @throws \InvalidArgumentException
     * @return string
     */
    function clickUrl($entity)
    {
        if ($entity instanceof Product) {
            return $this->clickUrl->prepareProductClickUrl($entity);
        }
        if ($entity instanceof Offer) {
            return $this->clickUrl->prepareOfferClickUrl($entity);
        }
        throw new \InvalidArgumentException("unsupported entity " . get_class($entity) ." for generate click");
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'clickUrl';
    }
} 