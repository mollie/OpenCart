<?php
namespace comercia;
class Request
{
    function get()
    {
        static $get = false;
        if (!$get) {
            $get = new ArrayObject(Util::registry("load")->get("request")->get);
        }
        return $get;
    }

    function post()
    {
        static $post = false;
        if (!$post) {
            $contentType = $this->getHeaderByName("content-type");
            if (strpos($contentType, "json") !== false) {
                $postData = json_decode($this->getRawData(), true);
                $post = new ArrayObject($postData);
            } else {
                $post = new ArrayObject(Util::registry("load")->get("request")->post);
            }
        }
        return $post;
    }

    function server()
    {
        static $server = false;
        if (!$server) {
            $getServer = Util::registry("load")->get("request")->server;
            $protocol = array("protocol"=>(!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://");
            $mergeArrays = array_merge($getServer, $getServer);
            $server = new ArrayObject($mergeArrays);
        }
        return $server;
    }

    public function getRawData()
    {
        return file_get_contents("php://input");
    }

    public function getHeaders()
    {
        if (function_exists("getallheaders")) {
            return array(array_change_key_case(getallheaders(), CASE_LOWER));
        } else {
            return array(array_change_key_case($this->getallheadersFallback(), CASE_LOWER));
        }
    }

    function getallheadersFallback()
    {
        static $headers = false;
        if (!$headers) {
            $headers = [];
            foreach ($_SERVER as $name => $value) {
                if (substr($name, 0, 5) == 'HTTP_') {
                    $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
                }
            }
        }
        if (isset($_SERVER["CONTENT_TYPE"])) {
            $headers["content-type"] = $_SERVER["CONTENT_TYPE"];
        }
        return $headers;
    }

    public function getHeaderByName($name)
    {
        $header = $this->getHeaders();

        if (!is_array($header)) {
            $header = $this->headersToArray($header);
        }

        //if the content type is set
        if (isset($header[0][$name])) {
            return $header[0][$name];
        }
        return "";
    }

    public function headersToArray($headerContent)
    {
        $result = array();

        //split on double enter
        $lines = explode("\r\n\r\n", $headerContent);

        for ($i = 0; $i < count($lines) - 1; $i++) {
            //split on single enter
            foreach (explode("\r\n", $lines[$i]) as $lKey => $line) {
                if ($lKey === 0) {
                    $result[$i]['http_code'] = $line;
                } else {
                    list ($key, $value) = explode(': ', $line);
                    $result[$i][strtolower($key)] = $value;
                }
            }
        }

        return $result;
    }



    public function getClientLanguage()
    {
        return substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
    }

    public function getIp()
    {
        return @$_SERVER['HTTP_CLIENT_IP'] ? $_SERVER['HTTP_CLIENT_IP'] : (@$_SERVER['HTTP_X_FORWARDE‌​D_FOR'] ? $_SERVER['HTTP_X_FORWARDED_FOR'] : (@$_SERVER['REMOTE_ADDR'] ? $_SERVER['REMOTE_ADDR'] : "127.0.0.1"));
    }

    public function getUrl()
    {
        return (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    }
}

?>