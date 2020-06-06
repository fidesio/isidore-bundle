<?php

namespace Fidesio\IsidoreBundle\Services;

use Doctrine\Common\Cache\CacheProvider;
use Doctrine\Common\Cache\FilesystemCache;
use Doctrine\Common\Cache\RedisCache;
use Snc\RedisBundle\DependencyInjection\Configuration\RedisDsn;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Cache
{
    const EXTENSION = '.fidesioisidorebundle.data';
    const DOCTRINE_NAMESPACE_CACHEKEY = 'FidesioIsidoreBundle[%s]';

    /**
     * @var CacheProvider
     */
    private $cacheDriver;

    public function __construct(ContainerInterface $container)
    {
        $enable = $container->getParameter('fidesio_isidore.cache.enable');
        if (!$enable) {
            return;
        }

        $type = $container->getParameter('fidesio_isidore.cache.type');
        if ($type === 'file') {
            $rootDir = $container->getParameter('kernel.root_dir');
            $env = $container->getParameter('kernel.environment');
            $directory = $rootDir.'/cache/'.$env.'/isidore';

            $this->cacheDriver = new FilesystemCache($directory, self::EXTENSION);
        } elseif ($type === 'redis') {
            $dsn = new RedisDsn($container->getParameter('fidesio_isidore.cache.redis'));

            $redis = new \Redis();
            $redis->connect($dsn->getHost(), $dsn->getPort());
            $redis->select($dsn->getDatabase());

            $this->cacheDriver = new RedisCache();
            $this->cacheDriver->setRedis($redis);
        }
    }


    /**
     * Tests if an entry exists in the cache.
     *
     * @param string $id The cache id of the entry to check for.
     * @return bool TRUE if a cache entry exists for the given cache id, FALSE otherwise.
     */
    public function has($id)
    {
        return $this->cacheDriver !== null ? $this->cacheDriver->contains($id) : false;
    }

    /**
     * Fetches an entry from the cache.
     *
     * @param string $id The id of the cache entry to fetch.
     * @return mixed|false The cached data or FALSE, if no cache entry exists for the given id.
     */
    public function get($id)
    {
        return $this->cacheDriver !== null ? $this->cacheDriver->fetch($id) : false;
    }

    /**
     * Puts data into the cache.
     *
     * @param string $id       The cache id.
     * @param string $data     The cache entry/data.
     * @param int    $lifeTime The lifetime. If != 0, sets a specific lifetime for this
     * @return bool TRUE if the entry was successfully stored in the cache, FALSE otherwise.
     */
    public function set($id, $data, $lifeTime = 0)
    {
        return $this->cacheDriver !== null ? $this->cacheDriver->save($id, $data, $lifeTime) : false;
    }

    /**
     * Deletes a cache entry.
     *
     * @param string $id The cache id.
     * @return bool TRUE if the cache entry was successfully deleted, FALSE otherwise.
     */
    public function delete($id)
    {
        return $this->cacheDriver !== null ? $this->cacheDriver->delete($id) : false;
    }

    public function flush()
    {
        return $this->cacheDriver !== null ? $this->cacheDriver->flushAll() : false;
    }
}
