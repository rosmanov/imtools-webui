<?php
namespace ImTools\WebUI;

class Request
{
    public static function getIntFromGet($key, $default_value = 0, $min = null, $max = null)
    {
        $n = isset($_GET[$key]) ? (int) $_GET['key'] : $default_value;
        return (($min !== null && $n < $min) || ($max !== null && $n > $max)) ? $default_value : $n;
    }
}
