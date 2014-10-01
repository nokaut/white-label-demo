<?php

namespace WL\AppBundle\Lib\View\Data\Converter;

use Nokaut\ApiKit\Collection\Products;

interface ConverterInterface
{
    public function convert(Products $products);
} 