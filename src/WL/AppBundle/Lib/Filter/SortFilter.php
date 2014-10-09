<?php
/**
 * Created by PhpStorm.
 * User: jjuszkiewicz
 * Date: 02.10.2014
 * Time: 10:30
 */

namespace WL\AppBundle\Lib\Filter;


use Nokaut\ApiKit\Collection\Products;

class SortFilter
{
    protected static $mapping = array(
        "Domyślne" => "domyślne",
        "Najpopularniejsze" => "od popularnych",
        "Najmniej popularne" => "od mało popularnych",
        "Od a do z" => "od a do z",
        "Od z do a" => "od z do a",
        "Najtańsze" => "od najtańszych",
        "Najdroższe" => "od najdroższych"
    );

    public function filter(Products $products)
    {
        if (!$products->getMetadata()->getSorts()) {
            $products->getMetadata()->setSorts(array());
            return;
        }

        $sorts = array();

        foreach (self::$mapping as $apiName => $mappingName) {
            foreach ($products->getMetadata()->getSorts() as $sort) {
                if ($sort->getName() == $apiName) {
                    $sort->setName($mappingName);
                    $sorts[] = $sort;
                }
            }
        }

        $products->getMetadata()->setSorts($sorts);
    }

} 