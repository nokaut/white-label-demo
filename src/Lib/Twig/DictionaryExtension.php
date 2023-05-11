<?php
/**
 * Created by PhpStorm.
 * User: jjuszkiewicz
 * Date: 08.10.2014
 * Time: 09:49
 */

namespace App\Lib\Twig;


use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class DictionaryExtension extends AbstractExtension
{
    public function getFilters()
    {
        return array(
            new TwigFilter('varietyProducts', [$this, 'varietyProducts']),
        );
    }

    /**
     * @param $number
     * @return string
     */
    function varietyProducts($number)
    {
        $listOfVariety = array('produkt', 'produkty', 'produktów');

        if ($number == 0) {
            return $number . " " . $listOfVariety[2];
        }
        if ($number == 1) {
            return $number . " " . $listOfVariety[0];
        }
        if ($number < 5) {
            return $number . " " . $listOfVariety[1];
        }
        if ($number <= 21) {
            return $number . " " . $listOfVariety[2];
        }
        $mod = $number % 10;
        if ($mod >= 2 && $mod <= 4) {
            return $number . " " . $listOfVariety[1];
        } else {
            return $number . " " . $listOfVariety[2];
        }
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