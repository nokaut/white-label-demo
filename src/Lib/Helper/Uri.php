<?php
/**
 * Created by PhpStorm.
 * User: jjuszkiewicz
 * Date: 17.09.2014
 * Time: 16:48
 */

namespace App\Lib\Helper;


class Uri
{
    public static function prepareApiUrl($url)
    {
        if (empty($url)) {
            return $url;
        }
        return ltrim($url, '/');
    }
} 