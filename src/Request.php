<?php
namespace ImTools\WebUI;

class Request
{
    public static function getIntFromGet($key, $default_value = 0, $min = null, $max = null)
    {
        return self::_getInt($_GET, $key, $default_value, $min, $max);
    }

    public static function getIntFromPost($key, $default_value = 0, $min = null, $max = null)
    {
        return self::_getInt($_POST, $key, $default_value, $min, $max);
    }

    private static function _getInt($source, $key, $default_value = 0, $min = null, $max = null) {
        $n = isset($source[$key]) ? (int) $source[$key] : $default_value;
        return (($min !== null && $n < $min) || ($max !== null && $n > $max)) ? $default_value : $n;
    }

    public static function redirect($url) {
        header('HTTP/1.1 301 Moved Permanently');
        header('Location: ' . $url);
        exit();
    }

    public static function requirePostString($key, $error_message = null)
    {
        if (! (isset($_POST[$key]) && $name = trim($_POST[$key]))) {
            if (!$error_message) {
                $error_message = 'Missing required POST variable "' . $key . '"';
            }
            throw new \RuntimeException($error_message);
        }
    }
}
