<?php
/**
 * FileManager.php
 * By FIDESIO <http://wwww.fidesio.com> <contact@fidesio.com>
 * Agence Digitale & Technique
 *
 * @author Harouna MADI <harouna.madi@fidesio.com>
 */

namespace Fidesio\IsidoreBundle\Services;

use CURLFile;
use finfo;
use InvalidArgumentException;
use RuntimeException;

final class FileManager
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * FileManager constructor.
     *
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @param string      $filepath
     * @param string|null $type set mime type
     *
     * @return array|null
     */
    public function send($filepath, $type = null)
    {
        if (empty($type)) {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $type = $finfo->file($filepath);
        }

        if (!is_string($filepath)) {
            throw new InvalidArgumentException('$file_url doit être une chaîne de caractères.');
        }

        $getData = [];
        $url = 'upload/';
        $postData = ['|file[]' => new CURLFile($filepath, trim($type, ';'))];
        $addToken = true;

        $this->client->buildURL($url, $getData, $postData, $addToken);

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLINFO_HEADER_OUT    => true,
            CURLOPT_HEADER         => true,
            CURLOPT_POST           => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_POSTFIELDS     => $postData,
        ]);

        if ($this->client->getAuthBasicUser() && $this->client->getAuthBasicPass()) {
            curl_setopt(
                $ch,
                CURLOPT_USERPWD,
                $this->client->getAuthBasicUser() . ':' . $this->client->getAuthBasicPass()
            );
        }

        $res = explode("\r\n\r\n", curl_exec($ch));

        $info = curl_getinfo($ch);
        $info = [
            'request'  => [
                'url'          => $info['url'],
                'header'       => curl_getinfo($ch, CURLINFO_HEADER_OUT),
                'post_data'    => json_encode($postData),
                'content_type' => $info['content_type'],
            ],
            'response' => [
                'header'    => $res[0],
                'http_code' => $info['http_code'],
                'data'      => json_decode($res[1], true),
            ],
        ];
        curl_close($ch);

        $this->client->setLastRequest($info['request']);
        $this->client->setLastResponse($info['response']);

        if ($info['response']['http_code'] !== 200) {
            $this->client->getLogger()->critical('CURL Request failed' . $url);

            if ($this->client->isDebugMode()) {
                throw new RuntimeException('CURL Request failed.' . $url);
            }
        }

        $jsonData = json_decode($res[2], true);

        return isset($jsonData['data'][0]) ? $jsonData['data'][0] : null;
    }
}
