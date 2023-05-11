<?php
/**
 * Created by PhpStorm.
 * User: jjuszkiewicz
 * Date: 05.09.2014
 * Time: 13:01
 */

namespace App\Lib\Type\Menu;


class Link
{
    /**
     * @var string
     */
    protected $name;
    /**
     * @var string
     */
    protected $url;

    function __construct($url, $name)
    {
        $this->url = $url;
        $this->name = $name;
    }

    /**
     * @param string $href
     */
    public function setUrl($href)
    {
        $this->url = $href;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

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

}