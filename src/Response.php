<?php
namespace ImTools\WebUI;

class Response
{
    public function badRequest($body = null)
    {
        header('HTTP/1.1 400 Bad Request', true, 400);
        exit($body);
    }

    public function internalServerError($body = null)
    {
        header('HTTP/1.1 500 Internal Server Error', true, 500);
        exit($body);
    }

}
