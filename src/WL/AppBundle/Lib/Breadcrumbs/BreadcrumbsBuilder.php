<?php
/**
 * Created by PhpStorm.
 * User: jjuszkiewicz
 * Date: 05.09.2014
 * Time: 10:59
 */

namespace WL\AppBundle\Lib\Breadcrumbs;


use Nokaut\ApiKit\Entity\Category;
use Nokaut\ApiKit\Ext\Data\Collection\Filters\FiltersAbstract;
use Nokaut\ApiKit\Ext\Data\Entity\Filter\FilterAbstract;
use WL\AppBundle\Lib\Type\Breadcrumb;

class BreadcrumbsBuilder
{
    /**
     * @param Category $category
     * @param $functionPrepareUrl
     * @return Breadcrumb[]
     */
    public function prepareBreadcrumbs(Category $category, $functionPrepareUrl)
    {
        $breadcrumbs = array();
        foreach ($category->getPath() as $path) {
            $breadcrumbs[] = new Breadcrumb(
                $path->getTitle(),
                $functionPrepareUrl($path->getUrl())
            );
        }
        return $breadcrumbs;
    }

    /**
     * @param Breadcrumb[] $breadcrumbs
     * @param FiltersAbstract[] $filters
     * @return Breadcrumb[]
     */
    public function appendFilter(&$breadcrumbs, $filters)
    {
        $breadcrumbsFilers = '';
        foreach ($filters as $filter) {
            $breadcrumbsFilers .= $filter->getName() ? $filter->getName() . ": " : "";
            foreach ($filter as $value) {
                /** @var FilterAbstract $value */
                $breadcrumbsFilers .= $value->getName() . ($filter->getUnit() ? ' ' . $filter->getUnit() : '');
                $breadcrumbsFilers .= ', ';
            }
        }
        if ($breadcrumbsFilers) {
            $breadcrumbs[] = new Breadcrumb(trim($breadcrumbsFilers, ', '));
            return $breadcrumbs;
        }
    }
} 