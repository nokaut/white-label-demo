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
    /**
     * @var Categories
     */
    protected $categoriesAllowed;

    function __construct(CategoriesRepository $categoriesRepository , CategoriesAllowed $categoriesAllowed)
    {
        $this->categoriesAllowed = $categoriesRepository->fetchCategoriesByIds($categoriesAllowed->getAllowedCategories());
    }


    public function getReduceUrl($url)
    {
        $categoriesUrlPart = $this->joinCategoriesUrls();

        $cutUrl = str_replace($categoriesUrlPart, '', $url);
        return '/'.ltrim($cutUrl, '/');
    }

    /**
     * add categories to url for filter only for allowed categories
     * @param string $phrase
     * @return string
     */
    public function preparePhraseWithAllowCategories($phrase)
    {
        $phrase = $this->clearPhraseUrl($phrase);

        if ($this->hasCategory($phrase)) {
            return $phrase;
        }

        $categoriesUrlPart = $this->joinCategoriesUrls();
        return $categoriesUrlPart . '/' . $phrase;
    }

    /**
     * @param string $phrase
     * @return string
     */
    public function clearPhraseUrl($phrase)
    {
        $phrase = str_replace(
            array('ę', 'ó', 'ą', 'ś', 'ł', 'ż', 'ź', 'ć', 'ń'),
            array('e', 'o', 'a', 's', 'l', 'z', 'z', 'c', 'n'),
            $phrase);
        $phrase = preg_replace('/\s+/', ' ', $phrase);
        return $phrase;
    }

    /**
     * @param string $phrase
     * @return bool
     */
    protected function hasCategory($phrase)
    {
        $phraseParts = explode('/', ltrim($phrase, '/'));

        return count($phraseParts) > 1;
    }

    /**
     * @param Categories
     * @return string
     */
    protected function joinCategoriesUrls()
    {
        CategoriesSort::sortBy($this->categoriesAllowed,
            function (Category $category) {
                return $category->getUrl();
            }
        );

        $categoriesUrlPart = "";
        foreach ($this->categoriesAllowed as $category) {
            /** @var Category $category */
            $categoriesUrlPart .= trim($category->getUrl(), '/') . ",";
        }
        return rtrim($categoriesUrlPart, ',');
    }
} 