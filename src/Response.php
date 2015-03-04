<?php
namespace ImTools\WebUI;

class Response
{
    public static function badRequest($body = null)
    {
        header('HTTP/1.1 400 Bad Request', true, 400);
        exit($body);
    }

    public static function internalServerError($body = null)
    {
        header('HTTP/1.1 500 Internal Server Error', true, 500);
        exit($body);
    }

    public static function json(array $array)
    {
        header('Content-Type: application/json');
        exit(json_encode($array));
    }

    public static function jsonSuccess()
    {
        static::json(['error' => false, 'success' => true]);
    }
}
