<?php

namespace WL\AppBundle\Lib\View\Data\Collection\Filters;

abstract class PropertyAbstract extends FiltersAbstract
{
    /**
     * @return bool
     */
    public function isPropertyRanges()
    {
        return $this instanceof PropertyRanges;
    }

    /**
     * @return bool
     */
    public function isPropertyValues()
    {
        return $this instanceof PropertyValues;
    }
} 