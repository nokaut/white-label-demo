<?php
/**
 * Created by PhpStorm.
 * User: jjuszkiewicz
 * Date: 12.10.2016
 * Time: 11:33
 */

namespace WL\AppBundle\Lib\Menu;


use WL\AppBundle\Lib\Type\Menu\Link;

interface MenuInterface
{
    /**
     * @return string
     */
    public function getTemplate();
    /**
     * @return Link[]
     */
    public function getMenuLinks();
}