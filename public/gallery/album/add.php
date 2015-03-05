<?php
use ImTools\WebUI\Template;
use ImTools\WebUI\Gallery;
use ImTools\WebUI\Page;
use ImTools\WebUI\Request;
use ImTools\WebUI\Conf;

require_once __DIR__ . '/../../../src/bootstrap.php';


if (!empty($_POST)) {
    if (!($id = Gallery::addAlbum($_POST))) {
        $template_vars['errors'] = 'Failed to add album';
    }
    Request::redirect('/gallery/album/?id=' . $id);
}

$template_vars['gallery_menu'] = Page::get(null, 'gallery-pages');
$template_vars['page'] = Page::get('album-add', 'gallery-pages');
$template_vars['formats'] = Conf::get('thumbs');
$template_vars['interpolation'] = Conf::get('interpolation');
Template::display('gallery/album-add.twig', $template_vars);
