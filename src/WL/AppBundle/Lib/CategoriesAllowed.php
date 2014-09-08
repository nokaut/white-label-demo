<?php
/**
 * Created by PhpStorm.
 * User: jjuszkiewicz
 * Date: 05.09.2014
 * Time: 11:16
 */

namespace WL\AppBundle\Lib;


class CategoriesAllowed
{
    private $parametersCategories;

    function __construct($parametersCategories)
    {
        $this->parametersCategories = $parametersCategories;
    }

    public function getAllowedCategories()
    {
        $allowedCategories = array();

        foreach ($this->parametersCategories as $groupCategories) {
            $allowedCategories = array_merge($allowedCategories, $groupCategories);
        }
        return $allowedCategories;
    }

    /**
     * @return array
     */
    public function getParametersCategories()
    {
        return $this->parametersCategories;
    }


} 