<?php
namespace ImTools\WebUI;

use \ImTools\WebUI\WSCommand;

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

            $wsclient_config = Conf::get('wsclient');
            foreach (['application', 'port', 'host'] as $c) {
                self::$_global_vars['ws']['_config'][$c] = isset($wsclient_config[$c]) ? $wsclient_config[$c] : null;
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

            self::$_twig->addFunction(new \Twig_SimpleFunction('it_checkbox', function($params) {
                return '<div class="checkbox-wrapper" '
                    . (isset($params['id']) ? ' id="' . $params['id'] . '" ' : '')
                    . (!empty($params['hidden']) ? ' style="display:none" ' : '') .'>'
                    . '<input type="checkbox" name="' . $params['name'] . '"'
                    . (!empty($params['checked']) ? ' checked' : '')
                    . ' value=1/><span class="action">'
                    . ($params['desc'])
                    . '</span></div>';
            }, ['is_safe' => ['html']]));

            self::$_twig->addFunction(new \Twig_SimpleFunction('it_text_field', function($params) {
                return
                    '<div class="text-field-wrapper' . (isset($params['class']) ? ' ' . $params['class'] : '') . '" '
                    . (!empty($params['hidden']) ? ' style="display:none" ' : '')
                    . (isset($params['id']) ? ' id="' . $params['id'] . '" ' : '')
                    . '>'
                    . '<input type="text" '
                    . (isset($params['name']) ? ' name="' . $params['name'] . '" ' : '')
                    . (isset($params['value']) ? ' value="' . $params['value'] . '" ' : '')
                    . (isset($params['size']) ? ' size="' . $params['size'] . '" ' : '')
                    . (isset($params['maxlength']) ? ' maxlength="' . $params['maxlength'] . '" ' : '')
                    . '/><span class="action">' . ($params['desc'])
                    . '</span></div>';
            }, ['is_safe' => ['html']]));

            self::$_initialized = true;
        }
    }

    public static function display($template_name, array $vars = null)
    {
        echo self::render($template_name, $vars);
    }

    public static function render($template_name, array $vars = null)
    {
        return self::$_twig->render($template_name,
            ($vars ? array_merge(self::$_global_vars, $vars) : self::$_global_vars));
    }

    public static function addGlobals(array $new_globals)
    {
        self::$_global_vars = array_merge(self::$_global_vars, $new_globals);
    }

}
