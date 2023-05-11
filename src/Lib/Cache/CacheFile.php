<?php
/**
 * Created by PhpStorm.
 * User: jjuszkiewicz
 * Date: 04.04.2014
 * Time: 17:54
 */

namespace App\Lib\Cache;


use Desarrolla2\Cache\File;
use Desarrolla2\Cache\Memory as Cache;
use Nokaut\ApiKit\Cache\CacheInterface;

class CacheFile extends Cache implements CacheInterface
{

    private $enabledCache = true;
    protected $cacheDir;
    private $timeout;
    private $keyPrefix;

    function __construct($cacheDir, $timeout, $enabledCache = true, $keyPrefix = 'api-raw-response-')
    {
        $adapter = new File($cacheDir);
        $adapter->setTtlOption($timeout);
        $this->enabledCache = $enabledCache;
        $this->cacheDir = $cacheDir;
        $this->timeout = $timeout;
        $this->keyPrefix = md5($keyPrefix);
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
        return $this->keyPrefix;
    }

}
