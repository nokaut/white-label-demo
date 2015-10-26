<?php
/**
 * Created by PhpStorm.
 * User: jjuszkiewicz
 * Date: 04.04.2014
 * Time: 17:54
 */

namespace WL\AppBundle\Lib\Cache;


use Desarrolla2\Cache\Adapter\File;

class CacheFile extends \Desarrolla2\Cache\Cache implements \Nokaut\ApiKit\Cache\CacheInterface
{

    private $enabledCache = true;
    private $cacheDir;
    private $timeout;

    function __construct($cacheDir, $timeout, $enabledCache = true)
    {
        $adapter = new File($cacheDir);
        $adapter->setOption('ttl', $timeout);
        $this->enabledCache = $enabledCache;
        $this->cacheDir = $cacheDir;
        $this->timeout = $timeout;
        parent::__construct($adapter);
    }


    public function get($keyName, $lifetime = null)
    {
        if ($this->enabledCache) {
            return parent::get($keyName);
        }
        return null;
    }

    public function save($keyName = null, $content = null, $lifetime = null)
    {
        if ($this->enabledCache) {
            parent::set($keyName, $content, $lifetime);
        }
    }

    public function delete($keyName)
    {
        parent::delete($keyName);
    }

    public function getPrefixKeyName()
    {
        return 'api-raw-response-';
    }


} 