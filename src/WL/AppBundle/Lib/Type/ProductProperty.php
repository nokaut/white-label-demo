<?php
/**
 * Created by PhpStorm.
 * User: jjuszkiewicz
 * Date: 14.07.2014
 * Time: 21:43
 */

namespace WL\AppBundle\Lib\Type;


use Nokaut\ApiKit\Entity\Product\Property;

class ProductProperty extends Property
{
    /**
     * @var string
     */
    protected $url;

    /**
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

}