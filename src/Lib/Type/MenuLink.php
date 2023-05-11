<?php
/**
 * Created by PhpStorm.
 * User: jjuszkiewicz
 * Date: 05.09.2014
 * Time: 12:55
 */

namespace App\Lib\Type;


use App\Lib\Type\Menu\Link;
use Nokaut\ApiKit\Collection\Products;

class MenuLink extends Link
{
    protected $subLinks = array();
    protected $topProducts = array();

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

    /**
     * @param Products $topProducts
     */
    public function setTopProducts($topProducts)
    {
        $this->topProducts = $topProducts;
    }

    /**
     * @return Products
     */
    public function getTopProducts()
    {
        return $this->topProducts;
    }

}