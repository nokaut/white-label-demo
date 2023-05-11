<?php
/**
 * Created by PhpStorm.
 * User: jjuszkiewicz
 * Date: 23.07.2014
 * Time: 08:07
 */

namespace App\Lib\Cache;

use Desarrolla2\Cache\Memcached as MemcachedCache;
use Nokaut\ApiKit\Cache\CacheInterface;
use Memcached;

class Memcache implements CacheInterface
{
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
            $server = new Memcached();
            $server->addServer($host, $port);
            $this->cache = new MemcachedCache($server);
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