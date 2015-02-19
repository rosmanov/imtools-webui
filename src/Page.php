<?php
namespace ImTools\WebUI;

class Page
{
    public static function get($key = null, $subset = 'pages')
    {
        $pages = Conf::get($subset);
        return $key === null ? $pages : $pages[$key];
    }
}
