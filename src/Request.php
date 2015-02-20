<?php
namespace ImTools\WebUI;

class Request
{
    public static function getIntFromGet($key, $default_value = 0, $min = null, $max = null)
    {
        $n = isset($_GET[$key]) ? (int) $_GET[$key] : $default_value;
        return (($min !== null && $n < $min) || ($max !== null && $n > $max)) ? $default_value : $n;
    }

    public static function redirect($url) {
        header('HTTP/1.1 301 Moved Permanently');
        header('Location: ' . $url);
        exit();
    }
}
