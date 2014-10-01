<?php

namespace WL\AppBundle\Lib\View\Data\Converter\Filters\Selected;

use WL\AppBundle\Lib\View\Data\Converter\Filters\PropertiesConverterAbstract;
use WL\AppBundle\Lib\View\Data\Collection\Filters\PropertyAbstract;
use Nokaut\ApiKit\Collection\Products;

class PropertiesConverter extends PropertiesConverterAbstract
{
    /**
     * @param Products $products
     * @return PropertyAbstract[]
     */
    public function convert(Products $products)
    {
        $propertiesInitialConverted = $this->initialConvert($products);
        $properties = array();

        foreach ($propertiesInitialConverted as $property) {
            $selectedFilterEntities = $this->getSelectedFilterEntities($property);

            if (count($selectedFilterEntities) === 0) {
                continue;
            }

            $property->setEntities($selectedFilterEntities);

            $this->setPropertyIsNofollow($property, $propertiesInitialConverted);

            $properties[] = $property;
        }

        return $properties;
    }
}