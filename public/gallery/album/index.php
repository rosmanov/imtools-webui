<?php
use ImTools\WebUI\Template;
use ImTools\WebUI\Gallery;
use ImTools\WebUI\Request;
use ImTools\WebUI\Page;

require_once __DIR__ . '/../../../src/bootstrap.php';

if (!($id = Request::getIntFromGet('id', 0, 0))) {
    die('Invalid album ID');
}

$template_vars['gallery_menu'] = array_merge(Page::get(null, 'gallery-pages'), Page::get(null, 'album-pages'));
$template_vars['page'] = Page::get('gallery');


$template_vars['page']['name'] .= ' / Album';
if ($template_vars['album'] = Gallery::getAlbum($id)) {
    $template_vars['album']['images'] = Gallery::getImages($id);
}
Template::display('gallery/album-view.twig', $template_vars);
