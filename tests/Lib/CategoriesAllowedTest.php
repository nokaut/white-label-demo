<?php
/**
 * Created by PhpStorm.
 * User: jjuszkiewicz
 * Date: 29.10.2014
 * Time: 09:33
 */

namespace Tests\Lib;


use Nokaut\ApiKit\Entity\Category;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use App\Lib\Exception\CategoryNotAllowedException;
use App\Lib\CategoriesAllowed;

class CategoriesAllowedTest extends KernelTestCase
{
    public function testCheckAllowedCategory()
    {
        $cut = $this->preapreCut();
        $cut->expects($this->once())->method('getAllowedCategories')->willReturn(array(10, 20, 30, 40));

        $category = $this->prepareCategory();

        $cut->checkAllowedCategory($category);
    }

    public function testCheckAllowedCategoryNotAllowed()
    {
        $this->expectException(CategoryNotAllowedException::class);
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
     * @return MockObject
     */
    protected function preapreCut()
    {
        $cut = $this->getMockBuilder('App\Lib\CategoriesAllowed')
            ->onlyMethods(array('getAllowedCategories', 'isAllowedAllCategories'))
            ->disableOriginalConstructor()
            ->getMock();
        $cut->expects($this->any())->method('isAllowedAllCategories')->willReturn(false);
        return $cut;
    }
}
