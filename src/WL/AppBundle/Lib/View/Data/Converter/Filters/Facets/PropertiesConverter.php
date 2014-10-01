<?php


namespace WL\AppBundle\Lib\View\Data\Converter\Filters\Facets;

use WL\AppBundle\Lib\View\Data\Converter\Filters\PropertiesConverterAbstract;
use WL\AppBundle\Lib\View\Data\Collection\Filters\PropertyAbstract;
use WL\AppBundle\Lib\View\Data\Entity\Filter\FilterAbstract;
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
            $this->setPropertyIsActive($property);
            $this->setPropertyIsExcluded($property, $products->getMetadata()->getTotal());
            $this->setPropertyIsNofollow($property, $propertiesInitialConverted);
            $this->setPropertySort($property);

            $properties[] = $property;
        }

        return $properties;
    }

    /**
     * @param PropertyAbstract $property
     * @return FilterAbstract[]
     */
    public function getNonEmptyEntities(PropertyAbstract $property)
    {
        return array_filter($property->getEntities(), function ($entity) {
            return $entity->getTotal() > 0;
        });
    }

    /**
     * @param PropertyAbstract $property
     */
    protected function setPropertyIsActive(PropertyAbstract $property)
    {
        if ($this->countSelectedFiltersEntities($property) and $this->countSelectedFiltersEntities($property) < $property->count()) {
            $property->setIsActive(true);
            return;
        }

        $property->setIsActive(false);
    }

    /**
     * @param PropertyAbstract $property
     */
    protected function setPropertyIsExcluded(PropertyAbstract $property, $productsTotal)
    {
        $nonEmptyEntities = $this->getNonEmptyEntities($property);

        if (count($nonEmptyEntities) === 0) {
            $property->setIsExcluded(true);
            return;
        }

        if (count($nonEmptyEntities) === 1
            and current($nonEmptyEntities)->getIsFilter() === false
            and current($nonEmptyEntities)->getTotal() == $productsTotal) {
            $property->setIsExcluded(true);
            return;
        }

        $property->setIsExcluded(false);
    }

    /**
     * @param PropertyAbstract $property
     */
    protected function setPropertySort(PropertyAbstract $property)
    {
        if (!$property->count()) {
            return;
        }

        $entities = $property->getEntities();

        if (!is_numeric(substr($entities[0]->getName(), 0, 1)) or strstr($entities[0]->getName(), ' x ')) {
            usort($entities, function ($entity1, $entity2) {
                return strnatcmp($entity1->getName(), $entity2->getName());
            });
            $property->setEntities($entities);
        }
    }
} 