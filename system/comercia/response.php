<?php
namespace comercia;
class Response
{
    var $bufferMode=false;
    function redirect($route, $params = "", $ssl = true)
    {
        $url = Util::url()->link($route, $params, $ssl);
        $this->redirectToUrl($url);
    }

    function redirectBack(){
        $this->redirectToUrl(str_replace("&amp;","&",Util::request()->server()->HTTP_REFERER));
    }

    function redirectToUrl($url){
        Util::registry()->get("response")->redirect($url);
    }

    function addHeader($key, $value)
    {
        Util::registry()->get("response")->addHeader($key . ":" . $value);
    }

    function setCompression($level)
    {
        Util::registry()->get("response")->setCompression($level);
    }

    function view($view, $data = array(), $pageControllers = true)
    {
        if ($pageControllers) {
            Util::load()->pageControllers($data);
        }
        $result = Util::load()->view($view, $data);
        $this->write($result);
    }

    function write($output)
    {
        if($this->bufferMode){
            echo $output;
        }else {
            Util::registry()->get("response")->setOutput($output);
        }
    }

    function renderJson($data){
        $this->addHeader("content-type","application/json");
        $this->write(json_encode($data));
    }


    function toVariable($function){
        $oldBufferMode=  $this->bufferMode;
        $this->bufferMode=true;
        ob_start();
        $function();
        $result=ob_get_contents();
        ob_end_clean();
        $this->bufferMode=false;
        return $result;
    }
}

?>
