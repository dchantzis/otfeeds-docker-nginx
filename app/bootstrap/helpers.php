<?php

use Illuminate\Contracts\Encryption\EncryptException;
use Illuminate\Support\Collection;

if (!function_exists('customEncrypter'))
{

    /**
     * @param string $value
     * @return string
     * @throws Exception
     */
    function customEncrypter($value)
    {
        $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $cypher = sodium_crypto_secretbox(
            $value,
            $nonce,
            base64_decode(config('app.private_key'))
        );

        return bin2hex(
            $nonce . $cypher
        );

    }
}

if (!function_exists('customDecrypter')) {

    function customDecrypter(string $value)
    {
        $decoded = hex2bin($value);
        $nonce = mb_substr($decoded, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, '8bit');
        $cypher = mb_substr($decoded, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, null, '8bit');

        return sodium_crypto_secretbox_open(
            $cypher,
            $nonce,
            base64_decode(config('app.private_key'))
        );
    }

}

if (!function_exists('arrayToXml')) {

    /**
     * @param SimpleXMLElement $xmlObject
     * @param $data
     */
    function arrayToXml(&$xmlObject, $data)
    {
        foreach ($data as $key => $value) {

            if (is_array($value)) {

                $xmlNode = $xmlObject->addChild(ucwords($key));
                arrayToXml($xmlNode, $value);

            } elseif ($value instanceof Collection) {

                $xmlNode = $xmlObject->addChild(ucwords($key));
                arrayToXml($xmlNode, $value->toArray());

            } else {

                $xmlObject->addChild(ucwords($key), htmlspecialchars($value));

            }

        }
    }

}

