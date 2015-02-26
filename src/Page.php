<?php
namespace ImTools\WebUI;

class Page
{
    public static function get($key = null, $subset = 'pages')
    {
        $pages = Conf::get($subset);
        return $key === null ? $pages : $pages[$key];
    }

    public static function getMenus()
    {
        $menu = [];
        foreach (func_get_args() as $submenu) {
            $menu = array_merge($menu, Page::get(null, $submenu));
        }
        return $menu;
    }
}
