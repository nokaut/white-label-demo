<?php
/**
 * Created by PhpStorm.
 * User: jjuszkiewicz
 * Date: 04.04.2014
 * Time: 17:54
 */

namespace App\Lib\Cache;


use Nokaut\ApiKit\Cache\CacheInterface;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class CacheFile implements CacheInterface
{
    private $enabledCache = true;
    protected $cacheDir;
    private $keyPrefix;
    private $cache;

    function __construct($cacheDir, $timeout, $enabledCache = true, $keyPrefix = 'api-raw-response-')
    {
        $cache = new FilesystemAdapter('', $timeout, $cacheDir);
        $this->cache = $cache;
        $this->enabledCache = $enabledCache;
        $this->cacheDir = $cacheDir;
        $this->keyPrefix = md5($keyPrefix);
    }


    /**
     * @throws InvalidArgumentException
     */
    public function get($keyName, $lifetime = null): mixed
    {
        if ($this->enabledCache) {
            return $this->cache->get($keyName, function () {
                return null;
            });
        }
        return null;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function save($keyName = null, $content = null, $lifetime = null)
    {
        if ($this->enabledCache) {

            $cacheItem = $this->cache->getItem($keyName);
            $cacheItem->set($content);
            $cacheItem->expiresAfter($lifetime);

            $this->cache->save($cacheItem);
        }
    }

    public function delete($keyName)
    {
        $this->cache->delete($keyName);
    }

    public function getPrefixKeyName()
    {
        return $this->keyPrefix;
    }

}
