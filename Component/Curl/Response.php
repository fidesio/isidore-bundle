<?php

namespace Fidesio\IsidoreBundle\Component\Curl;

use Symfony\Component\HttpFoundation\Response as sfResponse;

/**
 * Class Response
 * @package Fidesio\IsidoreBundle\Component\Curl
 */
class Response extends sfResponse
{
    /**
     * @var mixed
     */
    protected $data;

    /**
     * Constructor.
     *
     * @param mixed $content The response content, see setContent()
     * @param int   $status  The response status code
     * @param array $headers An array of response headers
     *
     * @throws \InvalidArgumentException When the HTTP status code is not valid
     */
    public function __construct($content = '', $status = 200, $headers = [])
    {
        $res = explode("\r\n\r\n", $content);

        $rawHeaders = explode("\r\n", $res[0]);

        if(isset($rawHeaders[0]) && preg_match('@\s([0-9]{3})\s@', $rawHeaders[0], $matches)) {
            $status = (int) $matches[1];
        }

        $protocolVersion = (
            '1.'
            . (
            (isset($rawHeaders[0]) && preg_match('@HTTP\/1\.([0-1]{1})@si', $rawHeaders[0], $m)) ?
                $m[1] : '0'
            )
        );

        $headers = $this->parseRawHeaders($rawHeaders);

        $rawContent = $res[1];

        parent::__construct($rawContent, $status, $headers);

        $this->setProtocolVersion($protocolVersion);

        $this->data = (
            (isset($headers['Content-Type']) && $headers['Content-Type'] == 'application/json') ?
                json_decode($rawContent, true) : null
        );
    }

    /**
     * @param array $rawHeaders
     * @return array $headers
     */
    protected function parseRawHeaders($rawHeaders)
    {
        $headers = [];

        if (sizeof($rawHeaders)) {
            foreach ($rawHeaders as $rawHeader) {
                if (preg_match('@^([a-zA-Z\-\_]+)\:\s(.*)@', $rawHeader, $matches)) {
                    $headers[$matches[1]] = $matches[2];
                }
            }
        }

        return $headers;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }
}
