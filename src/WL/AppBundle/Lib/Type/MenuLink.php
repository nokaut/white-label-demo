<?php
/**
 * Created by PhpStorm.
 * User: jjuszkiewicz
 * Date: 05.09.2014
 * Time: 12:55
 */

namespace WL\AppBundle\Lib\Type;


use WL\AppBundle\Lib\Type\Menu\Link;

class MenuLink extends Link
{
    protected $subLinks = array();

    function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * @param Link $subLink
     */
    public function addSubLinks(Link $subLink)
    {
        $this->subLinks[] = $subLink;
    }

    /**
     * @param Link[] $subLinks
     */
    public function setSubLinks(array $subLinks)
    {
        $this->subLinks = $subLinks;
    }

    /**
     * @return Link[]
     */
    public function getSubLinks()
    {
        return $this->subLinks;
    }

}