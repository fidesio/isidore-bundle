<?php

namespace Fidesio\IsidoreBundle\Component\Curl;

use Symfony\Component\HttpFoundation\Response as sfResponse;

/**
 * Class Response
 * @package Fidesio\IsidoreBundle\Component\Curl
 */
final class Response extends sfResponse
{
    /**
     * @var array
     */
    protected $data = null;

    /**
     * Constructor.
     *
     * @param mixed $content The response content, see setContent()
     * @param int   $status The response status code
     * @param array $headers An array of response headers
     *
     * @throws \InvalidArgumentException When the HTTP status code is not valid
     */
    public function __construct($content, $status = self::HTTP_OK, array $headers = [])
    {
        if (empty($content)) {
            $content = '';
        } else {
            $res = explode("\r\n\r\n", $content);
            $rawHeaders = explode("\r\n", $res[0]);

            if (isset($rawHeaders[0]) && preg_match('@\s(\d{3})\s@', $rawHeaders[0], $matches)) {
                $status = (int)$matches[1];
            }

            $headers = array_merge($headers, $this->parseRawHeaders($rawHeaders));
            $content = $res[1];
        }

        parent::__construct($content, $status, $headers);

        if (isset($headers['Content-Type']) && $headers['Content-Type'] === 'application/json') {
            $protocolVersion = sprintf(
                '1.%s',
                (isset($rawHeaders[0]) && preg_match('@HTTP\/1\.([0-1]{1})@si', $rawHeaders[0], $m)) ? $m[1] : '0'
            );
            $this->setProtocolVersion($protocolVersion);
            $this->data = json_decode($content, true);
        }
    }

    /**
     * @param array $rawHeaders
     *
     * @return array $headers
     */
    protected function parseRawHeaders(array $rawHeaders)
    {
        $headers = [];

        if (count($rawHeaders)) {
            foreach ($rawHeaders as $rawHeader) {
                if (preg_match('@^([a-zA-Z\-\_]+)\:\s(.*)@', $rawHeader, $matches)) {
                    $headers[$matches[1]] = $matches[2];
                }
            }
        }

        return $headers;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }
}
