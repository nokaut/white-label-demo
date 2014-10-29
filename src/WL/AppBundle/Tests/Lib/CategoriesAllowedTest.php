<?php
/**
 * Created by PhpStorm.
 * User: jjuszkiewicz
 * Date: 29.10.2014
 * Time: 09:33
 */

namespace WL\AppBundle\Tests\Lib;


use Nokaut\ApiKit\Entity\Category;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CategoriesAllowedTest extends KernelTestCase
{
    public function testCheckAllowedCategory()
    {
        $cut = $this->preapreCut();
        $cut->expects($this->once())->method('getAllowedCategories')->willReturn(array(10, 20, 30, 40));

        $category = $this->prepareCategory();

        $cut->checkAllowedCategory($category);
    }

    /**
     * @expectedException \WL\AppBundle\Lib\Exception\CategoryNotAllowedException
     */
    public function testCheckAllowedCategoryNotAllowed()
    {
        $cut = $this->preapreCut();
        $cut->expects($this->once())->method('getAllowedCategories')->willReturn(array(10, 20, 40));

        $category = $this->prepareCategory();

        $cut->checkAllowedCategory($category);
    }

    /**
     * @return Category
     */
    protected function prepareCategory()
    {
        $path = new Category\Path();
        $path->setId(1);
        $pathList[] = $path;
        $path = new Category\Path();
        $path->setId(30);
        $pathList[] = $path;
        $path = new Category\Path();
        $path->setId(35);
        $pathList[] = $path;
        $category = new Category();
        $category->setId(35);
        $category->setTitle('kategoria test');
        $category->setPath($pathList);
        return $category;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function preapreCut()
    {
        $cut = $this->getMockBuilder('\WL\AppBundle\Lib\CategoriesAllowed')
            ->setMethods(array('getAllowedCategories'))
            ->disableOriginalConstructor()
            ->getMock();
        return $cut;
    }
} 