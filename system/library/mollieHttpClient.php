<?php
/**
 * This class is used by to send requests to the api
 * @author Mark Smit <m.smit@comercia.nl>
 */
class mollieHttpClient
{

    /**
     * Send a post request
     * @param string $url The url to send the request to
     * @param array $data The data to send to the server
     * @param string $token The session token
     */
    function post($url, $data, $token = false, $parse = true)
    {
        $ch = curl_init();

        $encoded=json_encode($data);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $encoded);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $headers = [
            'Content-Type:application/json'
        ];
        if ($token) {
            $headers[] = "token:" . $token;
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $server_output = curl_exec($ch);
        curl_close($ch);

        if($parse) {
            return json_decode($server_output, true);
        }else{
            return $server_output;
        }
    }

    /**
     * Send a get request
     * @param string $url The url to send the request to
     * @param string $token The session token
     */
    function get($url, $token = false,$parse=true)
    {
        global $is_in_debug;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch,CURLOPT_USERAGENT,"Mozilla/5.0");

        if ($token) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, ["token:" . $token]);
        };
        curl_setopt($ch,CURLOPT_FOLLOWLOCATION,true);

        $server_output = curl_exec($ch);
        curl_close($ch);
        if($parse) {
            return json_decode($server_output, true);
        }else{
            return $server_output;
        }
    }


}
?>