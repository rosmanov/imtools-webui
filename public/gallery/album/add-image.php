<?php
use ImTools\WebUI\Template;
use ImTools\WebUI\Gallery;
use ImTools\WebUI\Page;
use ImTools\WebUI\Request;

require_once __DIR__ . '/../../../src/bootstrap.php';

if (! ($album_id = Request::getIntFromGet('album_id', 0, 0))) {
    die('Invalid album ID');
}

if (!empty($_POST)) {
    if ($id = Gallery::addImage($_POST)) {
        Request::redirect('/gallery/image/?id=' . $id);
    } else {
        $template_vars['errors'] = 'Failed to add image';
    }
}

$template_vars['gallery_menu'] = Page::get(null, 'gallery-pages');
$template_vars['page'] = Page::get('album-add', 'gallery-pages');
$template_vars['album'] = Gallery::getAlbum($album_id);
Template::display('gallery/album-add.twig', $template_vars);
