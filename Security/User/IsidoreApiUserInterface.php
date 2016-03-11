<?php
/**
 * IsidoreApiUserInterface.php
 * By FIDESIO <http://wwww.fidesio.com> <contact@fidesio.com>
 * Agence Digitale & Technique
 *
 * @author Harouna MADI <harouna.madi@fidesio.com>
 */

namespace Fidesio\IsidoreBundle\Security\User;


interface IsidoreApiUserInterface
{

    public function setEmail($email);

    public function setUsername($username);

    public function setPassword($password);

    public function getId();

    public function getEmail();

    public function getPreference();

}
