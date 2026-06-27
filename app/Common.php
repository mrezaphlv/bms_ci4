<?php

/**
 * The goal of this file is to allow developers a location
 * where they can overwrite core procedural functions and
 * replace them with their own. This file is loaded during
 * the bootstrap process and is called during the framework's
 * execution.
 *
 * This can be looked at as a `master helper` file that is
 * loaded early on, and may also contain additional functions
 * that you'd like to use throughout your entire application
 *
 * @see: https://codeigniter.com/user_guide/extending/common.html
 */

use Config\Services;

if (! function_exists('encrypt')) {
    function encrypt($data): string
    {
        $encryptionKey = 'WebsecRahasia';
        $cipher        = 'AES-256-CBC';
        $iv            = openssl_random_pseudo_bytes(openssl_cipher_iv_length($cipher));
        $encrypted     = openssl_encrypt((string) $data, $cipher, $encryptionKey, 0, $iv);
        $result        = base64_encode($iv . $encrypted);

        return rtrim(strtr($result, '+/', '-_'), '=');
    }
}

if (! function_exists('decrypt')) {
    function decrypt(string $data): string|false
    {
        $encryptionKey = 'WebsecRahasia';
        $cipher        = 'AES-256-CBC';
        $payload       = base64_decode(strtr($data, '-_', '+/'));
        $ivLength      = openssl_cipher_iv_length($cipher);
        $iv            = substr($payload, 0, $ivLength);
        $encrypted     = substr($payload, $ivLength);

        return openssl_decrypt($encrypted, $cipher, $encryptionKey, 0, $iv);
    }
}

if (! function_exists('apiwebsec')) {
    function apiwebsec(string $method, string $endpoint, array $payload = []): object
    {
        $client = Services::curlrequest([
            'baseURI'     => rtrim(URL_WEBSEC, '/') . '/',
            'http_errors' => false,
            'timeout'     => 15,
            'verify'      => false,
            'headers'     => [
                'Accept'           => 'application/json',
                'Authorization'    => 'stulogin',
                'X-Requested-With' => 'XMLHttpRequest',
            ],
        ]);

        try {
            $response = $client->request(strtoupper($method), ltrim($endpoint, '/'), [
                'form_params' => $payload,
            ]);

            $body = (string) $response->getBody();
            $json = json_decode($body);

            if (is_object($json)) {
                return $json;
            }

            return (object) [
                'status' => false,
                'msg'    => $body !== '' ? $body : 'Invalid response from login API.',
            ];
        } catch (Throwable $e) {
            return (object) [
                'status' => false,
                'msg'    => $e->getMessage(),
            ];
        }
    }
}
