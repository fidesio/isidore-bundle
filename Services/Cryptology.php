<?php
/**
 * Cryptology.php
 * By FIDESIO <http://wwww.fidesio.com> <contact@fidesio.com>
 * Agence Digitale & Technique
 *
 * @author Harouna MADI <harouna.madi@fidesio.com>
 */

namespace Fidesio\IsidoreBundle\Services;

use Symfony\Component\Serializer\Exception\InvalidArgumentException;

class Cryptology
{
    /**
     * @param $publicKey
     * @param $cypher
     * @return string
     * @throws \Exception
     */
    public function rsaEncrypter($publicKey, $cypher)
    {
        $publicKey = base64_decode($publicKey);

        if (!$publicKey)
            throw new \Exception(openssl_error_string());

        openssl_public_encrypt(substr($cypher, 0, 21), $crypttext, $publicKey);

        return base64_encode($crypttext);
    }

    /**
     * @param $data
     * @param $key
     * @param int $base
     * @return mixed|string
     */
    public function sha1Sign($data, $key, $base = 16)
    {
        $signature = $this->hmacsha1($data, $key);

        if($base === 16){
            $signature = unpack('H*', $signature);
            return array_pop($signature);
        }
        if($base === 64){
            return base64_encode($signature);
        }

        throw new InvalidArgumentException('Unsupported base `'.$base.'`.');
    }

    /**
     * @param $data
     * @param $key
     * @return string
     */
    protected static function hmacsha1($data, $key)
    {
        $blocksize = 64;
        $hashfunc = 'sha1';

        if (strlen($key)>$blocksize)
            $key = pack('H*', $hashfunc($key));

        $key = str_pad($key,$blocksize,chr(0x00));
        $ipad = str_repeat(chr(0x36),$blocksize);
        $opad = str_repeat(chr(0x5c),$blocksize);

        $hmac = pack(
            'H*',$hashfunc(
                ($key^$opad).pack(
                    'H*',$hashfunc(
                        ($key^$ipad).$data
                    )
                )
            )
        );

        return $hmac;
    }
}
