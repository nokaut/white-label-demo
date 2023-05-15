<?php
/**
 * Created by PhpStorm.
 * User: jjuszkiewicz
 * Date: 23.07.2014
 * Time: 08:07
 */

namespace App\Lib\Cache;

use ErrorException;
use Nokaut\ApiKit\Cache\CacheInterface;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Cache\Adapter\MemcachedAdapter;
use Symfony\Component\Cache\Exception\CacheException;


class Memcache implements CacheInterface
{
    /** @var MemcachedAdapter $cache */
    private $cache;
    private $enabledCache;
    private $liveTime;
    private $keyPrefix;

    /**
     * @param string $host
     * @param string $port
     * @param int $liveTime - liveTime in seconds
     * @param bool $enabledCache
     * @param string $keyPrefix
     * @throws CacheException
     * @throws CacheException
     * @throws ErrorException
     */
    public function __construct($host, $port, $liveTime, $enabledCache = true, $keyPrefix = 'api-raw-response-')
    {
        if ($enabledCache) {
            $server = "memcached://memcached:{$port}";
            $client = MemcachedAdapter::createConnection($server);

            $cache = new MemcachedAdapter($client, '', 0);
            $this->cache = $cache;
        }
        $this->enabledCache = $enabledCache;
        $this->liveTime = $liveTime;
        $this->keyPrefix = md5($keyPrefix);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function get($keyName, $lifetime = null)
    {
        if ($this->enabledCache) {
            $cacheItem = $this->cache->getItem($keyName);

            if ($cacheItem->isHit()) {
                return $cacheItem->get();
            }
        }

        return null;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function save($keyName = null, $content = null, $lifetime = null)
    {
        if ($this->enabledCache) {
            if (empty($lifetime)) {
                $lifetime = $this->liveTime;
            }

            $cacheItem = $this->cache->getItem($keyName);
            $cacheItem->set($content);
            $cacheItem->expiresAfter($lifetime);
            $this->cache->save($cacheItem);
        }
    }

    /**
     * @throws InvalidArgumentException
     */
    public function delete($keyName)
    {
        if ($this->enabledCache) {
            $this->cache->deleteItem($keyName);
        }
    }

    public function getPrefixKeyName()
    {
        return $this->keyPrefix;
    }
}