<?php

namespace App\Lib\Helper;


class Ip
{
    /**
     * @return string - user IP
     */
    public static function getUserIp()
    {
        $userIp = null;

        if (empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $userIp = $_SERVER['REMOTE_ADDR'];
        } else if ($_SERVER['HTTP_X_FORWARDED_FOR'] != '') {
            $temp = $_SERVER['HTTP_X_FORWARDED_FOR'];
            $temp .= "," . $_SERVER['REMOTE_ADDR'];
            $temp = str_replace('|', ',', $temp);
            $temp = str_replace(';', ',', $temp);

            $temp_array = explode(',', $temp);
            $userIp = $temp_array[0];
        }
        if ($userIp == '::1') {
            $userIp = '127.0.0.1';
        }

        return $userIp;
    }

    /**
     * @param $ip
     * @return bool
     */
    public static function isGoogleIp($ip)
    {
        return (strpos($ip, "66.249.") === 0);
    }
}
