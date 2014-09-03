<?php
/**
 * Created by PhpStorm.
 * User: jjuszkiewicz
 * Date: 12.07.2014
 * Time: 14:24
 */

namespace WL\AppBundle\Lib\Type;


class Filter
{
    /**
     * @var string
     */
    protected $name;
    /**
     * @var mixed
     */
    protected $value;
    /**
     * @var string
     */
    protected $outUrl;

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string $outUrl
     */
    public function setOutUrl($outUrl)
    {
        $this->outUrl = $outUrl;
    }

    /**
     * @return string
     */
    public function getOutUrl()
    {
        return ltrim($this->outUrl, '/');
    }

}