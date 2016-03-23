<?php
/**
 * FileManager.php
 * By FIDESIO <http://wwww.fidesio.com> <contact@fidesio.com>
 * Agence Digitale & Technique
 *
 * @author Harouna MADI <harouna.madi@fidesio.com>
 */

namespace Fidesio\IsidoreBundle\Services;

use Fidesio\IsidoreBundle\Component\Exception\CurlException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Serializer\Exception\RuntimeException as Exception;
use Fidesio\IsidoreBundle\Services\Client;
use Fidesio\IsidoreBundle\Services\Auth;

class FileManager
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
     * @return Client
     */
    public function getClient()
    {
        return $this->container->get('fidesio_isidore.service.client');
    }

    /**
     * @return Auth
     */
    public function getAuth()
    {
        return $this->container->get('fidesio_isidore.service.auth');
    }

    /**
     * @param $filepath
     * @param string|null $type set mime type
     * @return mixed
     */
    public function send($filepath, $type = null)
    {
        if(empty($type)){
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $type = $finfo->file($filepath);
        }

        $getData = array();
        $url = 'upload/';
        $postData = array(
            '|file[]' => '@' . $filepath . ";type=".trim($type, ';').";"
        );
        $addToken = true;

        if (!is_string($filepath)) {
            throw new Exception('$file_url doit être une chaîne de caractères.');
        }

        $this->getClient()->buildURL($url, $getData, $postData, $addToken);
        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLINFO_HEADER_OUT => true,
            CURLOPT_HEADER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postData,
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

        $this->getClient()->setLastRequest($info['request']);
        $this->getClient()->setLastResponse($info['response']);

        if($info['response']['http_code'] != '200'){
            $this->getClient()->getLogger()->critical('CURL Request failed');

            if($this->getClient()->isDebugMode())
                throw new Exception('CURL Request failed.');
        }

        curl_close($ch);

        $jsonData = json_decode($res[2], true);

        return isset($jsonData['data'][0]) ? $jsonData['data'][0] : null;
    }
}
