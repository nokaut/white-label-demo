<?php
/**
 * Created by PhpStorm.
 * User: jjuszkiewicz
 * Date: 19.09.2014
 * Time: 21:11
 */

namespace App\Lib\Helper;


use App\Lib\CategoriesAllowed;
use Nokaut\ApiKit\Collection\Categories;
use Nokaut\ApiKit\Collection\Sort\CategoriesSort;
use Nokaut\ApiKit\Entity\Category;
use Nokaut\ApiKit\Repository\CategoriesRepository;

class UrlSearchAllowedCategories extends UrlSearch
{
    /**
     * @var Categories
     */
    protected $categoriesAllowed;
    protected $joinedCategoriesUrls;

    function __construct(CategoriesRepository $categoriesRepository, CategoriesAllowed $categoriesAllowed)
    {
        $this->categoriesAllowed = $categoriesRepository->fetchCategoriesByIds($categoriesAllowed->getAllowedCategories());
        $this->joinedCategoriesUrls = $this->joinCategoriesUrls();
    }


    public function getReduceUrl($url)
    {
        $cutUrl = str_replace($this->joinedCategoriesUrls, '', $url);
        return ltrim($cutUrl, '/');
    }

    /**
     * add categories to url for filter only for allowed categories
     * @param string $phrase
     * @return string
     */
    public function preparePhrase($phrase)
    {
        $phrase = $this->clearPhraseUrl($phrase);

        if ($this->hasCategory($phrase)) {
            return $phrase;
        }

        return $this->joinedCategoriesUrls . '/' . $phrase;
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