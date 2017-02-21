<?php
/**
 * Created by PhpStorm.
 * User: jjuszkiewicz
 * Date: 23.07.2014
 * Time: 08:07
 */

namespace WL\AppBundle\Lib\Cache;


use Lsw\MemcacheBundle\Cache\AntiDogPileMemcache;
use Nokaut\ApiKit\Cache\AbstractCache;
use Nokaut\ApiKit\Cache\CacheInterface;

class Memcache implements CacheInterface
{
    /**
     * @var \Memcache
     */
    private $cache;
    private $liveTime;
    private $enabledCache;
    private $keyPrefix;

    /**
     * @param string $host
     * @param string $port
     * @param int $liveTime - liveTime in seconds
     * @param bool $enabledCache
     * @param string $keyPrefix
     */
    public function __construct($host, $port, $liveTime, $enabledCache = true, $keyPrefix = 'api-raw-response-')
    {
        if ($enabledCache) {
            $this->cache = new \Memcached();
            $this->cache->addserver($host, $port);
            $this->liveTime = $liveTime;
        }
        $this->enabledCache = $enabledCache;
        $this->keyPrefix = md5($keyPrefix);
    }

    public function get($keyName, $lifetime = null)
    {
        if ($this->enabledCache) {
            return $this->cache->get($keyName);
        }
        return null;
    }

    public function save($keyName = null, $content = null, $lifetime = null)
    {
        if ($this->enabledCache) {
            if (empty($lifetime)) {
                $lifetime = $this->liveTime;
            }
            $this->cache->set($keyName, $content, $lifetime);
        }
    }

    public function delete($keyName)
    {
        if ($this->enabledCache) {
            $this->cache->delete($keyName);
        }
    }

    public function getPrefixKeyName()
    {
        return $this->keyPrefix;
    }

}