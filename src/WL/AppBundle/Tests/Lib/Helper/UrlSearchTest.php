<?php
/**
 * Created by PhpStorm.
 * User: jjuszkiewicz
 * Date: 30.09.2014
 * Time: 14:10
 */

namespace WL\AppBundle\Tests\Lib\Helper;


use Nokaut\ApiKit\Collection\Categories;
use Nokaut\ApiKit\Entity\Category;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use WL\AppBundle\Lib\Helper\UrlSearch;

class UrlSearchTest extends KernelTestCase
{
    /**
     * @var UrlSearch
     */
    protected $cut;

    /**
     * @before
     */
    public function init()
    {
        $mockCategoriesAllowed = $this->getMockBuilder('\WL\AppBundle\Lib\CategoriesAllowed')->disableOriginalConstructor()->getMock();
        $mockCategoriesAllowed->expects($this->once())->method('getAllowedCategories')->will($this->returnValue(array()));
        $mockRepo = $this->getMockBuilder('\Nokaut\ApiKit\Repository\CategoriesRepository')->disableOriginalConstructor()->getMock();
        $mockRepo->expects($this->once())->method('fetchCategoriesByIds')->will($this->returnValue($this->getAllowedCategories()));

        $this->cut = new UrlSearch($mockRepo, $mockCategoriesAllowed);
    }

    public function testGetReduceUrl()
    {
        $url = "/url-1,url-2,url3/produkt:casio.html";

        $urlReduced = $this->cut->getReduceUrl($url);

        $this->assertEquals('produkt:casio.html', $urlReduced);
    }

    public function testPreparePhraseWithAllowCategories()
    {
        $url = "produkt:casio.html";

        $phraseUrl = $this->cut->preparePhraseWithAllowCategories($url);

        $this->assertEquals("url-1,url-2,url3/produkt:casio.html", $phraseUrl);
    }

    protected function getAllowedCategories()
    {
        $categories = array();
        $category = new Category();
        $category->setUrl("/url-1");
        $categories[] = $category;
        $category = new Category();
        $category->setUrl("/url-2");
        $categories[] = $category;
        $category = new Category();
        $category->setUrl("/url3");
        $categories[] = $category;


        return new Categories($categories);
    }
} 