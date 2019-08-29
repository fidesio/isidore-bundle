<?php
/**
 * IsidoreApiUserProvider.php
 * By FIDESIO <http://wwww.fidesio.com> <contact@fidesio.com>
 * Agence Digitale & Technique
 *
 * @author Harouna MADI <harouna.madi@fidesio.com>
 */

namespace Fidesio\IsidoreBundle\Security\User;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Fidesio\IsidoreBundle\Services\Auth;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class IsidoreApiUserProvider implements UserProviderInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param $username
     * @param $password
     *
     * @return IsidoreApiUser
     */
    public function loadUser($username, $password)
    {
        try {
            $userData = $this->container->get('fidesio_isidore.service.auth')->getUserData();
            $apiUsername = $this->container->getParameter('fidesio_isidore.client.login');

            if (
                empty($userData) ||
                (isset($userData['username']) && ($userData['username'] == $apiUsername || @$userData['username'] != $username))
            ) {
                $auth = $this->container->get('fidesio_isidore.service.auth')->authentify($username, $password);
                $userData = $auth->getUserData();
            }

            if (!empty($userData)) {
                return new IsidoreApiUser($userData);
            }
        } catch (\Exception $e) {
            throw new UsernameNotFoundException($e->getMessage());
        }

        throw new UsernameNotFoundException("Identifiant ou mot de passe incorrect. Veuillez réessayer.");
    }

    public function loadUserByUsername($username)
    {
    }

    /**
     * @param UserInterface $user
     *
     * @return IsidoreApiUser
     */
    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof IsidoreApiUser) {
            throw new UnsupportedUserException(
                sprintf('Instances of "%s" are not supported.', get_class($user))
            );
        }

        return $this->loadUser($user->getUsername(), $user->getPassword());
    }

    /**
     * @param string $class
     *
     * @return bool
     */
    public function supportsClass($class)
    {
        return $class === 'Fidesio\IsidoreBundle\Security\User\IsidoreApiUser';
    }
}
