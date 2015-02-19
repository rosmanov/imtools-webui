<?php
use ImTools\WebUI\Template;
use ImTools\WebUI\Page;

require_once __DIR__ . '/../src/bootstrap.php';

$template_vars['page'] = Page::get('about');

Template::display('index.twig', $template_vars);
