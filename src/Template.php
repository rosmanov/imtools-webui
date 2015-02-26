<?php
namespace ImTools\WebUI;

class Template
{
    protected static
        /// Whether class is initialized
        $_initialized,
        /// Absolute path to directory where templates are stored
        $_templates_dir,
        /// Absolute path to directory where compiled templates will be stored
        $_templates_cache_dir,
        /// Instance of \Twig_Environment
        $_twig,
        /// Array of variables common for all templates
        $_global_vars;

    public static function init()
    {
        if (! self::$_initialized) {
            self::$_templates_dir = ROOT_DIR . '/templates';
            self::$_templates_cache_dir = ROOT_DIR . '/templates_cache';

            $k = 'common';
            self::$_global_vars = [$k => Conf::get($k), 'server' => $_SERVER];
            if ($_POST) {
                self::$_global_vars['post'] = $_POST;
            }
            if ($_GET) {
                self::$_global_vars['get'] = $_GET;
            }

            \Twig_Autoloader::register();

            $loader = new \Twig_Loader_Filesystem(self::$_templates_dir);
            self::$_twig = new \Twig_Environment($loader, [
                'cache' => self::$_templates_cache_dir,
                'debug' => self::$_global_vars['common']['debug'],
                'auto_reload' => true,
            ]);

            if (self::$_global_vars['common']['debug']) {
                self::$_twig->addExtension(new \Twig_Extension_Debug());
            }

            self::$_twig->addFunction(new \Twig_SimpleFunction('url_param_replace', function($params) {
                parse_str($_SERVER['QUERY_STRING'], $cur_params);

                $params = array_merge($cur_params, $params);
                $params = array_filter($params);

                $pos = strpos($_SERVER['REQUEST_URI'], '?');
                if ($pos)
                    $url = substr($_SERVER['REQUEST_URI'], 0, $pos);
                else
                    $url = $_SERVER['REQUEST_URI'];

                if ($params)
                    $url .= '?' .http_build_query($params, '', '&');

                return $url;
            }));

            self::$_initialized = true;
        }
    }

    public static function display($template_name, array $vars = null)
    {
        self::$_twig->display($template_name,
            ($vars ? array_merge(self::$_global_vars, $vars) : self::$_global_vars));
    }

    public static function addGlobals(array $new_globals)
    {
        self::$_global_vars = array_merge(self::$_global_vars, $new_globals);
    }

}
