<?php
namespace ImTools\WebUI;

class Conf
{
    protected static
        /// Directory where configuration files are stored
        $_conf_dir,
        /// Storage for lazy loading
        $_cache,
        /// User-overridden configuration
        $_local_conf,
        /// Whether class is initialized
        $_initialized;

    public static function init()
    {
        if (! self::$_initialized) {
            self::$_conf_dir = ROOT_DIR . '/conf';
            self::$_local_conf = self::get('local', null, false);
            self::$_initialized = true;
        }
    }

    public static function get($name, $key = null, $panic = true)
    {
        if (! isset(self::$_cache[$name])) {
            self::_load($name, $panic);
        }
        $conf = self::$_cache[$name];

        if (isset(self::$_local_conf[$name])) {
            $conf = array_merge($conf, self::$_local_conf[$name]);
        }

        return $key !== null ? $conf[$key] : $conf;
    }

    private static function _load($name, $panic = true)
    {
        $path = self::$_conf_dir . '/' .  $name . '.php';
        if (! file_exists($path) && $panic) {
            throw new \RuntimeException("Configuration file doesn't exist: '$path'");
        }
        self::$_cache[$name] = require $path;
    }
}
