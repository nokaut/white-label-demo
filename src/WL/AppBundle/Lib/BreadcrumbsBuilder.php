<?php
/**
 * Created by PhpStorm.
 * User: jjuszkiewicz
 * Date: 05.09.2014
 * Time: 10:59
 */

namespace WL\AppBundle\Lib;


use Nokaut\ApiKit\Entity\Category;
use WL\AppBundle\Lib\Type\Breadcrumb;

class BreadcrumbsBuilder
{
   public function prepareBreadcrumbs(Category $category, $functionPrepareUrl, array $categoriesAllow)
   {
       $isAllowedToBreadcrumbs = false;
       $breadcrumbs = array();
       foreach ($category->getPath() as $path) {
           if ($isAllowedToBreadcrumbs == false && in_array($path->getId(), $categoriesAllow)) {
               $isAllowedToBreadcrumbs = true;
           }

           if ($isAllowedToBreadcrumbs) {
               $breadcrumbs[] = new Breadcrumb(
                   $path->getTitle(),
                   $functionPrepareUrl($path->getUrl())
               );
           }

       }
       return $breadcrumbs;
   }
} 