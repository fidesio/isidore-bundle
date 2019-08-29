<?php
/**
 * StoreRepositoryInterface.php
 * By FIDESIO <http://wwww.fidesio.com> <contact@fidesio.com>
 * Agence Digitale & Technique
 *
 * @author Harouna MADI <harouna.madi@fidesio.com>
 */

namespace Fidesio\IsidoreBundle\ORM;

interface StoreRepositoryInterface
{
    /**
     * Finds all objects in the repository.
     *
     * @return array
     */
    public function findAll();

    /**
     * Finds data in store.
     *
     * @param array      $criteria
     * @param array|null $orderBy
     * @param int|null   $limit
     * @param int|null   $offset
     *
     * @return array The objects.
     */
    public function find(array $criteria, array $orderBy = null, $limit = null, $offset = null);

    /**
     * Finds a single object by a set of criteria.
     *
     * @param array $criteria The criteria.
     *
     * @return array|object
     */
    public function findOneBy(array $criteria);
}
