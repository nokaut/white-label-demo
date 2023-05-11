<?php
/**
 * Created by PhpStorm.
 * User: jjuszkiewicz
 * Date: 05.11.2014
 * Time: 11:02
 */

namespace App\Lib\Filter;


use App\Lib\Helper\Uri;
use Nokaut\ApiKit\Entity\Category;

class UrlCategoryFilter
{
    public function filter(Category $category)
    {
        $url = Uri::prepareApiUrl($category->getUrl());
        $category->setUrl($url);

        $this->filterPath($category);
    }

    /**
     * @param Category $category
     */
    protected function filterPath(Category $category)
    {
        foreach ($category->getPath() as $path) {
            $url = Uri::prepareApiUrl($path->getUrl());
            $path->setUrl($url);
        }
    }
} 