<?php
/**
 * IsidoreApiUser.php
 * By FIDESIO <http://wwww.fidesio.com> <contact@fidesio.com>
 * Agence Digitale & Technique
 *
 * @author Harouna MADI <harouna.madi@fidesio.com>
 */

namespace Fidesio\IsidoreBundle\Security\User;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;

class IsidoreApiUser implements IsidoreApiUserInterface, UserInterface, EquatableInterface
{

    protected $id;
    protected $email;
    protected $username;
    protected $password;
    protected $roles;
//    protected $preferences;

    public function __construct(array $userData = [])
    {
        $this->id = isset($userData['id']) ? $userData['id'] : null;
        $this->email = isset($userData['email']) ? $userData['email'] : null;
        $this->username = isset($userData['username']) ? $userData['username'] : null;
        $this->password = isset($userData['password']) ? $userData['password'] : null;
        $this->roles = isset($userData['roles']) ? $userData['roles'] : null;
//        $this->preferences = isset($userData['preferences']) ? $userData['preferences'] : null;
    }

    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    public function setUsername($username)
    {
        $this->username = $username;
        return $this;
    }

    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getEmail()
    {
        return $this->email;
    }

//    public function getPreference()
//    {
//        return $this->preferences;
//    }

    public function getRoles()
    {
        return $this->roles;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function getSalt()
    {
        return null;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function eraseCredentials()
    {
    }

    public function isEqualTo(UserInterface $user)
    {
        if (!$user instanceof IsidoreApiUser) {
            return false;
        }

        if ($this->password !== $user->getPassword()) {
            return false;
        }

        if ($this->username !== $user->getUsername()) {
            return false;
        }

        return true;
    }

}
