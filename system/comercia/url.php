<?php
namespace comercia;
class Url
{
    function image($image)
    {
        if (Util::info()->IsInAdmin()) {
            if (defined(HTTPS_CATALOG)) {
                return HTTPS_CATALOG . "image/" . $image;
            }
            return HTTP_CATALOG . "image/" . $image;
        }
        if (defined(HTTPS_SERVER)) {
            return HTTPS_SERVER . "image/" . $image;
        }
        return HTTP_SERVER . "image/" . $image;
    }

    function catalog($route, $params = "", $ssl = true)
    {
        $url = $this->getCatalogUrl($ssl) . "index.php?route=" . $route;
        if ($params) {
            $url .= "&" . $params;
        }
        return $url;
    }

    function getCatalogUrl($ssl = true)
    {
        if (Util::info()->IsInAdmin()) {
            if (defined(HTTPS_CATALOG) && $ssl) {
                return HTTPS_CATALOG;
            }
            return HTTP_CATALOG;
        }
        if (defined(HTTPS_SERVER) && $ssl) {
            return HTTPS_SERVER;
        }
        return HTTP_SERVER;
    }

    function link($route, $params = "", $ssl = true)
    {
        $session = Util::session();

        if (Util::version()->isMinimal(3.0)) {
            $tokenName = "user_token";
        } else {
            $tokenName = "token";
        }

        if ($session->$tokenName && $session->user_id && strpos($params, "route=") === false) {
            if ($session->$tokenName) {
                if ($params) {
                    $params .= "&".$tokenName."=" . $session->$tokenName;
                } else {
                    $params = $tokenName."=" . $session->$tokenName;
                }
            }
        }

        if ($ssl && !HTTPS_SERVER) {
            $ssl = false;
        }

        $result = "";
        if (!$ssl) {
            $result = $this->_url()->link($route, $params);
        } else {
            if (Util::version()->isMinimal("2.2")) {
                $result = $this->_url()->link($route, $params, true);
            } else {
                $result = $this->_url()->link($route, $params, "ssl");
            }
        }

        return str_replace("&amp;", "&", $result);
    }

    private function _url()
    {
        $registry = Util::registry();
        if (!$registry->has('url')) {
            $registry->set('url', new Url(HTTP_SERVER));
        }

        return $registry->get("url");
    }
}

?>