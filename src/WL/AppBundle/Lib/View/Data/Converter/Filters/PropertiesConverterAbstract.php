<?php


namespace WL\AppBundle\Lib\View\Data\Converter\Filters;

use Nokaut\ApiKit\Entity\Metadata\Facet\PropertyFacet\Range;
use Nokaut\ApiKit\Collection\Products;
use WL\AppBundle\Lib\View\Data\Converter\ConverterInterface;
use WL\AppBundle\Lib\View\Data\Collection\Filters\FiltersAbstract;
use WL\AppBundle\Lib\View\Data\Entity\Filter\FilterAbstract as FilterAbstractEntity;
use WL\AppBundle\Lib\View\Data\Collection\Filters\PropertyAbstract;
use WL\AppBundle\Lib\View\Data\Collection\Filters\PropertyValues;
use WL\AppBundle\Lib\View\Data\Collection\Filters\PropertyRanges;
use WL\AppBundle\Lib\View\Data\Entity\Filter\PropertyRange;
use WL\AppBundle\Lib\View\Data\Entity\Filter\PropertyValue;

abstract class PropertiesConverterAbstract implements ConverterInterface
{
    /**
     * @param Products $products
     * @return PropertyAbstract[]
     */
    protected function initialConvert(Products $products)
    {
        $facetProperties = $products->getProperties();

        $properties = array();

        foreach ($facetProperties as $facetProperty) {
            $entities = array();
            if ($facetProperty->getRanges()) {
                foreach ($facetProperty->getRanges() as $range) {
                    $entity = new PropertyRange();
                    $entity->setName($this->getPropertyRangeName($range));
                    $entity->setUrl($range->getUrl());
                    $entity->setIsFilter($range->getIsFilter());
                    $entity->setTotal($range->getTotal());
                    $entity->setMin($range->getMin());
                    $entity->setMax($range->getMax());
                    $entities[] = $entity;
                }

                $property = new PropertyRanges($entities);
            } else {
                foreach ($facetProperty->getValues() as $value) {
                    $entity = new PropertyValue();
                    $entity->setName($value->getName());
                    $entity->setUrl($value->getUrl());
                    $entity->setIsFilter($value->getIsFilter());
                    $entity->setTotal($value->getTotal());
                    $entities[] = $entity;
                }

                $property = new PropertyValues($entities);
            }

            $property->setUnit($facetProperty->getUnit());
            $property->setName($facetProperty->getName());
            $property->setId($facetProperty->getId());

            $properties[] = $property;
        }

        return $properties;
    }

    /**
     * @param PropertyAbstract $property
     * @return int
     */
    public function countSelectedFiltersEntities(PropertyAbstract $property)
    {
        return count($this->getSelectedFilterEntities($property));
    }

    /**
     * @param PropertyAbstract $property
     * @param PropertyAbstract[] $properties
     * @internal param int $selectedFiltersLimit
     */
    protected function setPropertyIsNofollow(PropertyAbstract $property, $properties = array())
    {
        if ($property->isPropertyRanges()) {
            foreach ($property as $value) {
                $value->setIsNofollow(true);
            }
            return;
        }

        // Jesli jakikolwiek poza biezacym property ma zaznaczone wicej niz jedna wartosc - nofollow
        if($this->isAnyPropertyMultiSelected($properties,$property)){
            foreach ($property as $value) {
                $value->setIsNofollow(true);
            }
            return;
        }

        // Jesli dany property ma zaznaczona jakas ceche
        // ale jesli jest filtrem to gdy ma zaznaczone wiecej niz dwie wartosci
        if ($this->countSelectedFiltersEntities($property) > 0) {
            foreach ($property as $value) {
                if($value->getIsFilter() and $this->countSelectedFiltersEntities($property) <= 2){
                    $value->setIsNofollow(false);
                }else{
                    $value->setIsNofollow(true);
                }
            }
            return;
        }

        foreach ($property as $value) {
            $value->setIsNofollow(false);
        }
    }

    /**
     * @param PropertyAbstract[] $properties
     */
    protected function isAnyPropertyMultiSelected($properties, PropertyAbstract $skipProperty = null)
    {
        /** PropertyAbstract $property */
        foreach ($properties as $property) {
            if ($property->getId() != $skipProperty->getId() and $this->countSelectedFiltersEntities($property) > 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param FiltersAbstract $property
     * @return FilterAbstractEntity[]
     */
    protected function getSelectedFilterEntities(FiltersAbstract $property)
    {
        return array_filter($property->getEntities(), function ($entity) {
            return $entity->getIsFilter();
        });
    }

    /**
     * @param Range $range
     * @return string
     */
    protected function getPropertyRangeName(Range $range)
    {
        if ($range->getMin() == $range->getMax()) {
            return (string)$range->getMin();
        } else {
            return sprintf("%s - %s", $range->getMin(), $range->getMax());
        }
    }
} 