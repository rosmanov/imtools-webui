<?php
use ImTools\WebUI\Template;
use ImTools\WebUI\Gallery;
use ImTools\WebUI\Page;
use ImTools\WebUI\Request;

require_once __DIR__ . '/../../../src/bootstrap.php';


if (!empty($_POST)) {
    if (!($id = Gallery::addAlbum($_POST))) {
        $template_vars['errors'] = 'Failed to add album';
    }
    Request::redirect('/gallery/');
}

$template_vars['gallery_menu'] = Page::get(null, 'gallery-pages');
$template_vars['page'] = Page::get('album-add', 'gallery-pages');
Template::display('gallery/album-add.twig', $template_vars);
