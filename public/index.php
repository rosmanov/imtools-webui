<?php
use ImTools\WebUI\Template;
use ImTools\WebUI\Page;
use ImTools\WebUI\CommandFactory;
use ImTools\WebUI\WSCommandFactory;

require_once __DIR__ . '/../src/bootstrap.php';

$template_vars['page'] = Page::get('about');

$command = CommandFactory::create(CommandFactory::CMD_VERSION, []);
$template_vars['imtools']['version'] = $command->run();

foreach (['all', 'version', 'features'] as $subcommand) {
    $arguments = [ 'subcommand' => $subcommand ];
    $ws_command = WSCommandFactory::create(WSCommandFactory::CMD_META, $arguments);
    $template_vars['wscmd']['meta'][$subcommand] = json_encode([
        'command'   => 'meta',
        'arguments' => $arguments,
        'digest'    => $ws_command->generateDigest(),
    ]);
}

Template::display('index.twig', $template_vars);
