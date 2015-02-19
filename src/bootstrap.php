<?php
namespace ImTools\WebUI;

define (__NAMESPACE__ . '\ROOT_DIR', __DIR__ . '/../');
require_once ROOT_DIR . '/vendor/autoload.php';

Conf::init();
Template::init();

Template::addGlobals(['menu' => Page::get(null)]);
