<?php
/**
 * Created by PhpStorm.
 * User: jjuszkiewicz
 * Date: 19.09.2014
 * Time: 21:11
 */

namespace WL\AppBundle\Lib\Helper;


use Nokaut\ApiKit\Collection\Categories;
use Nokaut\ApiKit\Collection\Sort\CategoriesSort;
use Nokaut\ApiKit\Entity\Category;
use Nokaut\ApiKit\Repository\CategoriesRepository;
use WL\AppBundle\Lib\CategoriesAllowed;

class UrlSearch
{
    public function getReduceUrl($url)
    {
        return ltrim($url, '/');
    }

    /**
     * add categories to url for filter only for allowed categories
     * @param string $phrase
     * @return string
     */
    public function preparePhrase($phrase)
    {
        $phrase = $this->clearPhraseUrl($phrase);
        return $phrase;
    }

    /**
     * @param string $phrase
     * @return string
     */
    public function clearPhraseUrl($phrase)
    {
        $phrase = preg_replace('/\s+/', ' ', $phrase);
        return $phrase;
    }

}
