<?php
/**
 * StoreInterface.php
 * By FIDESIO <http://wwww.fidesio.com> <contact@fidesio.com>
 * Agence Digitale & Technique
 *
 * @author Harouna MADI <harouna.madi@fidesio.com>
 */

namespace Fidesio\IsidoreBundle\ORM;


interface StoreInterface
{

    public function __toString();

    /**
     * Get store name
     *
     * @return string
     */
    public function getStoreName();

    /**
     * Get store name
     *
     * @return string
     */
    public function getStoreMetaName();



}
