<?php
/**
 * Created by PhpStorm.
 * User: jjuszkiewicz
 * Date: 08.10.2014
 * Time: 09:49
 */

namespace WL\AppBundle\Lib\Twig;


class DictionaryExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            'varietyProducts' => new \Twig_Filter_Method($this, 'varietyProducts'),
        );
    }

    /**
     * @param $count
     * @return string
     */
    function varietyProducts($count)
    {
        if ($count == 1) {
            return $count . " produkt";
        }
        if ($count > 1 && $count < 5) {
            return $count . " produkty";
        }
        return $count . " produktów";
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'dictionary';
    }
} 