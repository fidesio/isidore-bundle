<?php

namespace Fidesio\IsidoreBundle\Services;

use Doctrine\Common\Cache\FilesystemCache as DoctrineFilesystemCache;

class Cache extends DoctrineFilesystemCache
{
    const EXTENSION = '.fidesioisidorebundle.data';
    const DOCTRINE_NAMESPACE_CACHEKEY = 'FidesioIsidoreBundle[%s]';

    /**
     * Tests if an entry exists in the cache.
     *
     * @param string $id The cache id of the entry to check for.
     * @return bool TRUE if a cache entry exists for the given cache id, FALSE otherwise.
     */
    public function has($id)
    {
        return $this->doContains($id);
    }

    /**
     * Fetches an entry from the cache.
     *
     * @param string $id The id of the cache entry to fetch.
     * @return mixed|false The cached data or FALSE, if no cache entry exists for the given id.
     */
    public function get($id)
    {
        return $this->doFetch($id);
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
        return $this->doSave($id, $data, $lifeTime);
    }

    /**
     * Deletes a cache entry.
     *
     * @param string $id The cache id.
     * @return bool TRUE if the cache entry was successfully deleted, FALSE otherwise.
     */
    public function delete($id)
    {
        return $this->doDelete($id);
    }

}