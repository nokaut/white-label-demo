<?php
/**
 * Created by PhpStorm.
 * User: jjuszkiewicz
 * Date: 22.07.2014
 * Time: 08:37
 */

namespace WL\AppBundle\Lib\Helper;


use Nokaut\ApiKit\Entity\Metadata\Facet\PriceFacet;
use Nokaut\ApiKit\Entity\Metadata\Facet\ProducerFacet;
use Nokaut\ApiKit\Entity\Metadata\Facet\PropertyFacet;

class SelectedFilter
{
    /**
     * @param PropertyFacet $property
     * @return bool
     */
    public function isSelectedProperty(PropertyFacet $property)
    {
        foreach ($property->getValues() as $value) {
            if ($value->getIsFilter()) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param ProducerFacet[] $producers
     * @return bool
     */
    public function isSelectedProducer(array $producers)
    {
        foreach ($producers as $producer) {
            if ($producer->getIsFilter()) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param PriceFacet[] $prices
     * @return bool
     */
    public function isSelectedPrices(array $prices)
    {
        foreach ($prices as $price) {
            if ($price->getIsFilter()) {
                return true;
            }
        }
        return false;
    }
} 