<?php

namespace Fidesio\IsidoreBundle\Services;

use Exception;
use Fidesio\IsidoreBundle\Component\Curl\Response;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class Client
 * @package Fidesio\IsidoreBundle\Services
 */
final class Client
{
    protected $baseURL;
    protected $authBasicUser;
    protected $authBasicPass;
    protected $stores = [];
    protected $lastRequest;
    protected $lastResponse;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var bool
     */
    protected $debug;


    public function __construct(ContainerInterface $container, LoggerInterface $logger)
    {
        $this->container = $container;
        $this->logger = $logger;
        $this->baseURL = $this->container->getParameter('fidesio_isidore.client.url');
        $this->authBasicUser = $this->container->getParameter('fidesio_isidore.client.auth_basic_user');
        $this->authBasicPass = $this->container->getParameter('fidesio_isidore.client.auth_basic_pass');
        $this->debug = $this->container->getParameter('kernel.debug');
    }

    /**
     * @return Auth
     */
    public function getAuth()
    {
        return $this->container->get('fidesio_isidore.service.auth');
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }

    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @return bool
     */
    public function isDebugMode()
    {
        return $this->debug;
    }

    /**
     * @return mixed
     */
    public function getBaseURL()
    {
        return $this->baseURL;
    }

    /**
     * @param mixed $baseURL
     *
     * @return $this
     */
    public function setBaseURL($baseURL)
    {
        $this->baseURL = $baseURL;

        return $this;
    }

    public function setLastRequest($lastRequest)
    {
        $this->lastRequest = $lastRequest;

        return $this;
    }

    public function setLastResponse($lastResponse)
    {
        $this->lastResponse = $lastResponse;

        return $this;
    }

    public function getLastRequest()
    {
        return $this->lastRequest;
    }

    public function getLastResponse()
    {
        return $this->lastResponse;
    }

    /**
     * @return string
     */
    public function getAuthBasicUser()
    {
        return $this->authBasicUser;
    }

    /**
     * @param string $authBasicUser
     */
    public function setAuthBasicUser($authBasicUser)
    {
        $this->authBasicUser = $authBasicUser;
    }

    /**
     * @return string
     */
    public function getAuthBasicPass()
    {
        return $this->authBasicPass;
    }

    /**
     * @param string $authBasicPass
     */
    public function setAuthBasicPass($authBasicPass)
    {
        $this->authBasicPass = $authBasicPass;
    }

    /**
     * @param       $url
     * @param array $getData
     * @param array $postData
     * @param bool  $addToken
     *
     * @return mixed
     * @throws Exception
     */
    public function operate($url, array $getData = [], array $postData = [], $addToken = true)
    {
        if (!is_string($url)) {
            $this->logger->critical('$url doit être une chaîne de caractères.');
            if ($this->debug) {
                throw new InvalidArgumentException('$url doit être une chaîne de caractères.');
            }
        }
        if (!is_array($getData)) {
            $this->logger->critical('$getData doit être un tableau.');
            if ($this->debug) {
                throw new InvalidArgumentException('$getData doit être un tableau.');
            }
        }
        if (!is_array($postData)) {
            $this->logger->critical('$postData doit être un tableau.');
            if ($this->debug) {
                throw new InvalidArgumentException('$postData doit être un tableau.');
            }
        }

        $this->buildURL($url, $getData, $postData, $addToken);

        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLINFO_HEADER_OUT    => true,
            CURLOPT_HEADER         => true,
        ]);
        if ($this->getAuthBasicUser() && $this->getAuthBasicPass()) {
            curl_setopt($ch, CURLOPT_USERPWD, $this->getAuthBasicUser() . ":" . $this->getAuthBasicPass());
        }

        if (!empty($postData)) {
            curl_setopt_array($ch, [
                CURLOPT_POST       => true,
                CURLOPT_POSTFIELDS => json_encode($postData),
                CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Expect:'],
            ]);
        }

        $response = new Response(curl_exec($ch));
        $data = $response->getData();
        $info = curl_getinfo($ch);

        $info = [
            'request'  => [
                'url'          => $info['url'],
                'header'       => curl_getinfo($ch, CURLINFO_HEADER_OUT),
                'post_data'    => json_encode($postData),
                'content_type' => $info['content_type'],
            ],
            'response' => [
                'header'    => $response->headers->all(),
                'http_code' => $info['http_code'],
                'data'      => $data,
            ],
        ];

        curl_close($ch);

        $this->lastRequest = $info['request'];
        $this->lastResponse = $info['response'];
        if ($info['response']['http_code'] !== 200) {
            $message = (isset($data['exception']['message']) ? $data['exception']['message'] : 'CURL Request failed: ' . $url);
            $code = isset($data['exception']['code']) ? $data['exception']['code'] : 0;
            $this->logger->critical($message);
            if ($this->isDebugMode()) {
                throw new RuntimeException($message, $code);
            }
        }

        // Actuellement les contrôleurs s'attendent à ce que la propriété 'error' contienne le message d'erreur
        // et que la propriété 'code' contienne le code de l'exception.
        if (isset($data['exception'])) {
            if (!isset($data['error'])) {
                $data['error'] = $data['exception']['message'];
            }
            if (!isset($data['code'])) {
                $data['code'] = $data['exception']['code'];
            }
        }

        return $data;
    }

    /**
     * Génère l'url de la requuête en ajoutant le token de sécrité.
     *
     * @param $url
     * @param $getData
     * @param $postData
     * @param $addToken
     *
     * @return void
     */
    public function buildURL(&$url, &$getData, $postData, $addToken = true)
    {
        $authService = $this->getAuth();
        $url = $this->baseURL . $url;
        $url2 = $this->formatUrl($url);
        $queryURL = $url2;

        if (!empty($getData)) {
            $queryURL .= '?' . http_build_query($getData, '', '&', PHP_QUERY_RFC3986);
        }

        if ($authService->getTokenName() !== null
            && $authService->getTokenDelimiter() !== null
            && $authService->getUuid() !== null
        ) {
            $getData[$authService->getTokenName()] = $authService->getUuid();

            if ($addToken) {
                if (empty($postData)) {
                    $hash = sha1('GET' . $queryURL);
                } else {
                    if (isset($postData['|file[]']) && $postData['|file[]']) {
                        $hash = sha1('POST' . $queryURL);
                    } else {
                        $hash = sha1('POST' . $queryURL) . sha1(json_encode($postData));
                    }
                }
                $getData[$authService->getTokenName()] .= $authService->getTokenDelimiter() .
                    $authService->getCryptology()->sha1Sign($hash, $authService->getCredential());

            }

            $queryURL = $url . '?' . http_build_query($getData, '', '&', PHP_QUERY_RFC3986);
        }

        $url = $queryURL;
    }

    /**
     * @param string $newUrl
     *
     * @return string
     */
    protected function formatUrl($newUrl)
    {
        $pos = strripos($newUrl, '@');
        $auth = strripos($newUrl, '--auth');
        $login = strripos($newUrl, '--login');
        $getStores = strripos($newUrl, '--getStores');
        $get = strripos($newUrl, '--get');
        $read = strripos($newUrl, '--read');

        if ($auth === false
            && $getStores === false
            && $get === false
            && $login === false
            && !($pos === false)) {
            $http = stristr($newUrl, '://', true);
            $url = stristr($newUrl, '@');
            $url = substr($url, 1);
            $newUrl = $http . '://' . $url;
        }

        return $newUrl;
    }
}
