<?php
namespace Mollie;

class mollieHttpClient
{
    /**
     * Send a POST request via cURL
     * * @param string $url The url to send the request to
     * @param array $data The data to send to the server
     * @param string|bool $token The session token
     * @param bool $parse Whether to parse the JSON response
     * @return mixed
     * @throws \RuntimeException
     */
    public function post(string $url, array $data, string|bool $token = false, bool $parse = true): mixed
    {
        if (!function_exists('curl_init')) {
            throw new \RuntimeException('mollieHttpClient Error: CURL extension is not loaded in PHP.');
        }

        $ch = curl_init();
        $encoded = json_encode($data);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $encoded);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $headers = [
            'Content-Type: application/json'
        ];
        
        if ($token) {
            $headers[] = "token: " . $token;
        }
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $server_output = curl_exec($ch);
        $error = curl_error($ch);
        


        if ($server_output === false) {
            throw new \RuntimeException('mollieHttpClient POST Error: ' . $error);
        }

        if ($parse) {
            return json_decode((string)$server_output, true);
        }

        return $server_output;
    }

    /**
     * Send a GET request via cURL
     * * @param string $url The url to send the request to
     * @param string|bool $token The session token
     * @param bool $parse Whether to parse the JSON response
     * @return mixed
     * @throws \RuntimeException
     */
    public function get(string $url, string|bool $token = false, bool $parse = true): mixed
    {
        if (!function_exists('curl_init')) {
            throw new \RuntimeException('mollieHttpClient Error: CURL extension is not loaded in PHP.');
        }

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0");
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 5);

        if ($token) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, ["token: " . $token]);
        }

        $server_output = curl_exec($ch);
        $error = curl_error($ch);
        


        if ($server_output === false) {
            throw new \RuntimeException('mollieHttpClient GET Error: ' . $error);
        }

        if ($parse) {
            return json_decode((string)$server_output, true);
        }

        return $server_output;
    }
}