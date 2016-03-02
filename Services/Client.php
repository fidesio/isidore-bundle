<?php
/**
 * Client.php
 * By FIDESIO <http://wwww.fidesio.com> <contact@fidesio.com>
 * Agence Digitale & Technique
 *
 * @author Harouna MADI <harouna.madi@fidesio.com>
 */

namespace Fidesio\IsidoreBundle\Services;

use Symfony\Component\DependencyInjection\ContainerInterface,
    Symfony\Component\Serializer\Exception\RuntimeException as Exception,
    Monolog\Logger
;

class Client
{
    protected $baseURL;
    protected $stores = array();
    protected $lastRequest = null;
    protected $lastResponse = null;

    /**
     * @var \Symfony\Component\DependencyInjection\Container
     */
    protected $container;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var bool
     */
    protected $debug;


    public function __construct(ContainerInterface $container, Logger $logger)
    {
        $this->container = $container;
        $this->logger = $logger;
        $this->baseURL = $this->container->getParameter('fidesio_isidore.client.url');
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
     * @return \Symfony\Component\DependencyInjection\Container|ContainerInterface
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
     * @param $url
     * @param array $getData
     * @param array $postData
     * @param bool $addToken
     * @return mixed
     * @throws Exception
     */
    public function operate($url, array $getData = [], array $postData = [], $addToken = true)
    {
        if(!is_string($url)){
            $this->logger->critical('$url doit être une chaîne de caractères.');
            if($this->debug)
                throw new Exception('$url doit être une chaîne de caractères.');
        }
        if(!is_array($getData)){
            $this->logger->critical('$getData doit être un tableau.');
            if($this->debug)
                throw new Exception('$getData doit être un tableau.');
        }
        if(!is_array($postData)){
            $this->logger->critical('$postData doit être un tableau.');
            if($this->debug)
                throw new Exception('$postData doit être un tableau.');
        }

        $this->buildURL($url, $getData, $postData, $addToken);

        $ch = curl_init();

        curl_setopt_array($ch, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLINFO_HEADER_OUT => true,
            CURLOPT_HEADER => true,
        ));

        if(!empty($postData))
            curl_setopt_array($ch, array(
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($postData),
                CURLOPT_HTTPHEADER => array('Content-Type: application/json', 'Expect:'),
            ));

        $res = explode("\r\n\r\n", curl_exec($ch));

        $info = curl_getinfo($ch);

        $info = array(
            'request' => array(
                'url' => $info['url'],
                'header' => curl_getinfo($ch, CURLINFO_HEADER_OUT),
                'post_data' => json_encode($postData),
                'content_type' => $info['content_type']
            ),
            'response' => array(
                'header' => $res[0],
                'http_code' => $info['http_code'],
                'data' => json_decode($res[1], true)
            ),
        );

        $this->lastRequest = $info['request'];
        $this->lastResponse = $info['response'];

        if($info['response']['http_code'] != '200'){
            $this->logger->critical('CURL Request failed');

            if($this->debug)
                throw new Exception('CURL Request failed.');
        }

        curl_close($ch);

        return json_decode($res[1], true);

    }

    /**
     * Génère l'url de la requuête en ajoutant le token de sécrité.
     *
     * @param $url
     * @param $getData
     * @param $postData
     * @param $addToken
     * @return void
     */
    public function buildURL(&$url, &$getData, $postData, $addToken = true)
    {
        $authService = $this->getAuth();
        $url = $this->baseURL . $url;
        $url2 = $this->formatUrl($url);
        $queryURL = $url2;

        if(!empty($getData))
            $queryURL .= '?' . http_build_query($getData, '', '&');

        if($authService->getTokenName() !== null && $authService->getTokenDelimiter() !== null && $authService->getUuid() !== null){
            $getData[$authService->getTokenName()] = $authService->getUuid();

            if($addToken){
                if(empty($postData)){
                    $hash = sha1('GET' . $queryURL);
                }else{
                    if (isset($postData['|file[]']) && $postData['|file[]']) {
                        $hash = sha1('POST' . $queryURL);
                    } else {
                        $hash = sha1('POST' . $queryURL) . sha1(json_encode($postData));
                    }
                }
                $getData[$authService->getTokenName()] .= $authService->getTokenDelimiter() .
                    $authService->getCryptology()->sha1Sign($hash, $authService->getCredential());

            }

            $queryURL = $url . '?' . http_build_query($getData, '', '&');
        }

        $url = $queryURL;
    }

    /**
     * @param string $newUrl
     * @return string
     */
    protected function formatUrl($newUrl)
    {
        $pos = strripos($newUrl, "@");
        $auth = strripos($newUrl, "--auth");
        $login = strripos($newUrl, "--login");
        $getStores = strripos($newUrl, "--getStores");
        $get = strripos($newUrl, "--get");
        $read = strripos($newUrl, "--read");

        if (($auth === false) && ($getStores === false) && ($get === false) && ($login === false))
        {
            if (!($pos === false))
            {
                $http = stristr($newUrl, '://', true);
                $url=stristr($newUrl, '@');
                $url = substr($url, 1);
                $newUrl = $http . '://' . $url;
            }
        }

        return $newUrl;

    }

}
