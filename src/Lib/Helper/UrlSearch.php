<?php
/**
 * Created by PhpStorm.
 * User: jjuszkiewicz
 * Date: 19.09.2014
 * Time: 21:11
 */

namespace App\Lib\Helper;


class UrlSearch
{
    public function getReduceUrl($url)
    {
        if ($url !== null) {
            return ltrim($url, '/');
        }
        return $url;
    }

    /**
     * add categories to url for filter only for allowed categories
     * @param string $phrase
     * @return string
     */
    public function preparePhrase($phrase)
    {
        $phrase = $this->clearPhraseUrl($phrase);
        return $phrase;
    }

    /**
     * @param string $phrase
     * @return string
     */
    public function clearPhraseUrl($phrase)
    {
        $phrase = preg_replace('/\s+/', ' ', $phrase);
        return $phrase;
    }

}
