<?php
use ImTools\WebUI\Template;
use ImTools\WebUI\Page;

require_once __DIR__ . '/../src/bootstrap.php';

$template_vars['page'] = Page::get('about');

$command = (new \ImTools\WebUI\CommandFactory\Version())->requestCommand();
$template_vars['imtools']['version'] = $command->run();

Template::display('index.twig', $template_vars);
