<?php
/**
 * Created by PhpStorm.
 * User: jjuszkiewicz
 * Date: 23.09.2014
 * Time: 14:22
 */

namespace WL\AppBundle\Lib\Twig;


use Nokaut\ApiKit\Entity\Offer;
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
     * @param Offer $offer
     *
     * @return string
     */
    function clickUrl($offer)
    {
        return $this->clickUrl->prepareClickUrl($offer);
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