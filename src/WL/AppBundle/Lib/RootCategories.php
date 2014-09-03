<?php
/**
 * Created by PhpStorm.
 * User: jjuszkiewicz
 * Date: 12.07.2014
 * Time: 09:49
 */

namespace WL\AppBundle\Lib;


use Nokaut\ApiKit\ClientApi\Rest\Async\CategoriesAsyncFetch;
use Nokaut\ApiKit\Entity\Category;
use Nokaut\ApiKit\Repository\CategoriesAsyncRepository;

class RootCategories
{
    /**
     * @var CategoriesAsyncRepository
     */
    private $categoriesRepository;
    /**
     * @var CategoriesAsyncFetch
     */
    private $mainCategories;

    public function __construct(CategoriesAsyncRepository $categoriesRepository)
    {
        $this->categoriesRepository = $categoriesRepository;
        $this->fetchMainCategories();
    }

    public function getMainCategories()
    {
        return $this->mainCategories;
    }

    private function fetchMainCategories()
    {
        $this->mainCategories = $this->categoriesRepository->fetchByParentId(0);
    }
}