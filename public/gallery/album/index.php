<?php
use ImTools\WebUI\Template;
use ImTools\WebUI\Gallery;
use ImTools\WebUI\Request;
use ImTools\WebUI\Page;
use ImTools\WebUI\Conf;

require_once __DIR__ . '/../../../src/bootstrap.php';

if (!($id = Request::getIntFromGet('id', 0, 0))) {
    die('Invalid album ID');
}

$template_vars['page'] = Page::get('gallery');
$template_vars['gallery_menu'] = Gallery::getMenu(Gallery::MENU_TYPE_ALBUM);
$template_vars['page']['name'] .= ' / Album';
if ($template_vars['album'] = Gallery::getAlbum($id)) {
    $template_vars['page']['name'] .= ' / ' . $template_vars['album']['name'];
    $template_vars['album']['images'] = Gallery::getImages($id, true);
}
$template_vars['thumbnail_formats'] = Conf::get('thumbs');

Template::display('gallery/album-view.twig', $template_vars);
