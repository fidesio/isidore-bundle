<?php
/**
 * StoreTrait.php
 * By FIDESIO <http://wwww.fidesio.com> <contact@fidesio.com>
 * Agence Digitale & Technique
 *
 * @author Harouna MADI <harouna.madi@fidesio.com>
 */

namespace Fidesio\IsidoreBundle\ORM;

trait StoreTrait
{
    public function getStoreMetaName()
    {
        $storeName = self::getStoreName();

        return str_replace('::', '.', $storeName);
    }
}
