<?php
use ImTools\WebUI\Template;
use ImTools\WebUI\Page;
use ImTools\WebUI\CommandFactory;

require_once __DIR__ . '/../src/bootstrap.php';

$template_vars['page'] = Page::get('about');

$command = CommandFactory::create(CommandFactory::CMD_VERSION, []);
$template_vars['imtools']['version'] = $command->run();

Template::display('index.twig', $template_vars);
