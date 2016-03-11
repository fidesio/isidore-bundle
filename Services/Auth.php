<?php
/**
 * Auth.php
 * By FIDESIO <http://wwww.fidesio.com> <contact@fidesio.com>
 * Agence Digitale & Technique
 *
 * @author Harouna MADI <harouna.madi@fidesio.com>
 */

namespace Fidesio\IsidoreBundle\Services;

use Symfony\Component\DependencyInjection\ContainerInterface,
    Symfony\Component\Serializer\Exception\RuntimeException as Exception,
    Fidesio\IsidoreBundle\Component\Exception\CurlException,
    Monolog\Logger,
    Cake\Utility\Inflector;
;

/**
 * Class Auth
 * @package Fidesio\IsidoreBundle\Services
 */
class Auth
{

    protected $login;
    protected $password;
    protected $lastRequest = null;
    protected $lastResponse = null;
	

    /**
     * @var \Symfony\Component\DependencyInjection\Container
     */
    protected $container;

    /**
     * @var Cryptology
     */
    protected $cryptology;

    /**
     * @var Logger
     */
    protected $logger;

    public function __construct(ContainerInterface $container, Logger $logger)
    {
        $this->container = $container;
        $this->cryptology = $container->get('fidesio_isidore.service.cryptology');
        $this->logger = $logger;
        $this->debug = $this->container->getParameter('kernel.debug');
        $this->login = $this->container->getParameter('fidesio_isidore.client.login');
        $this->password = $this->container->getParameter('fidesio_isidore.client.password');
    }

    /**
     * Authentify as system
     *
     * @return Auth
     */
    public function authentifyAsSystem()
    {
        $this->login = $this->container->getParameter('fidesio_isidore.client.login');
        $this->password = $this->container->getParameter('fidesio_isidore.client.password');
        $this->authentify($this->login, $this->password);

        return $this;
    }

    /**
     * Authentify as regular user
     *
     * @return Auth|null
     */
    public function authentifyAsUser()
    {
        $user = $this->container->get('fidesio_user.user_manager')->getUser();

        if(!$user)
            return null;

        $this->login = $user->getUsername();
        $this->password = $user->getPassword();
        $this->authentify($this->login, $this->password);

        return $this;
    }

    /**
     * Permet de s'authentifier.
     *
     * @param string|null $login
     * @param string|null $password
     * @return $this
     * @throws Exception
     */
    public function authentify($login = null, $password = null)
    {
        $session = $this->container->get('session');
        $session->set('isidore.auth.credential', substr(sha1((rand() * 0xFF00FF) . (rand() * 0xFF00FF)), 0, 21));
        $client = $this->getClient();

        $authResult = $client->operate('controller/Fidesio.webservice.ServiceStore--auth');
        $session->set('isidore.auth.data', $authResult);
        $encryptedCredential = $this->cryptology->rsaEncrypter($this->getPublicKey(), $this->getCredential());
        $postData = array(
            'identifiant' => $login ? $login : $this->getLogin(),
            'password' => $password ? $password : $this->getPassword(),
            'public_credential' => $encryptedCredential,
            'remember' => null
        );

        try{
            $authUser = $client->operate('controller/Fidesio.controller.Authentification--login', [], $postData, false);
        }catch(CurlException $e){
            throw new Exception('authentify: ' . $e->getMessage());
        }

        if(!isset($authUser['success']) || $authUser['success'] != true){
            throw new Exception("Le serveur d'authentification ne répond pas. Veuillez reessayer ulterieurement.");
        }

        $session->set('isidore.auth.userData', $authUser);

        return $this;
    }

    /**
     * Reset password
     *
     * @param string $email
     * @return array
     */
    public function passwordReset($email)
    {
        $result = $this->getClient()->operate('controller/Fidesio.controller.Authentification--forgotPassword', [
            'mail' => $email,
            'g-recaptcha-response' => ""
        ]);

        return $result;
    }

    /**
     * @return $this
     */
    public function resetAuthSession()
    {
        $session = $this->container->get('session');
        $session->set('isidore.auth.data', null);
        $session->set('isidore.auth.userData', null);
        //$this->getClient()->operate('controller/Fidesio.controller.Authentification--logout');

        return $this;
    }

    /**
     * Get authentificated user data
     *
     * @return array|bool
     */
    public function getUserData()
    {
        $session = $this->container->get('session');
        $data = $session->get('isidore.auth.userData');

        if(!$data || !isset($data['id']))
            return false;

        $roles = [];

        if( isset($data['profil']) ){
            foreach($data['profil'] as $role){
                $roles[] = strtoupper(Inflector::underscore(strtolower("role_$role")));
            }
        }

        $userData = [
            'id' => (int)$data['id'],
            'username' => $data['identifiant'],
            'password' => $data['password'],
            'email' => $data['email'],
            'roles' => $roles,
            'preferences' => $data['preferences'],
        ];

        return $userData;
    }

    /**
     * @return Client|object
     */
    public function getClient()
    {
        return $this->container->get('fidesio_isidore.service.client');
    }

    /**
     * @return Cryptology|object
     */
    public function getCryptology()
    {
        return $this->cryptology;
    }

    /**
     * @return string
     */
    public function getCredential()
    {
        $session = $this->container->get('session');
        return $session->get('isidore.auth.credential');
    }

    /**
     * @return string|null
     */
    public function getUuid()
    {
        $res = $this->getResult();
        return isset($res['uuid']) ? $res['uuid'] : null;
    }

    /**
     * @return mixed
     */
    public function getLogin()
    {
        return $this->login;
    }

    /**
     * @param mixed $login
     * @return $this
     */
    public function setLogin($login)
    {
        $this->login = $login;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param mixed $password
     * @return $this
     */
    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getTokenName()
    {
        $res = $this->getResult();
        return isset($res['tokenName']) ? $res['tokenName'] : null;
    }

    /**
     * @return string|null
     */
    public function getTokenDelimiter()
    {
        $res = $this->getResult();
        return isset($res['tokenDelimiter']) ? $res['tokenDelimiter'] : null;
    }

    /**
     * @return array
     */
    public function getResult()
    {
        $session = $this->container->get('session');
        return $session->get('isidore.auth.data');
    }

    /**
     * @return string|null
     */
    public function getPublicKey()
    {
        $res = $this->getResult();
        return isset($res['publicKey']) ? $res['publicKey'] : null;
    }

    /**
     * @return bool
     */
    public function isAuthenticated()
    {
        $session = $this->container->get('session');
        return ($session->has('isidore.auth.userData') && $session->get('isidore.auth.userData') !== null);
    }

}
