<?php
/**
 * CurlException.php
 * By FIDESIO <http://wwww.fidesio.com> <contact@fidesio.com>
 * Agence Digitale & Technique
 *
 * @author Harouna MADI <harouna.madi@fidesio.com>
 */

namespace Fidesio\IsidoreBundle\Component\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;

class CurlException extends HttpException
{

    /**
     * @var array|int
     */
    public $info = [];

    /**
     * @param string $message
     * @param mixed $info
     */
    function __construct($message, $info = null)
    {
        $this->info = $info;
        $statusCode = !isset($this->info['response']['http_code']) ? 500 :$this->info['response']['http_code'];
        parent::__construct($statusCode, $message);
    }

    /**
     * @return array|int
     */
    public function getInfo()
    {
        return $this->info;
    }

}
