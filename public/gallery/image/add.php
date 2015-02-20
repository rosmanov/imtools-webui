<?php
use ImTools\WebUI\Template;
use ImTools\WebUI\Gallery;
use ImTools\WebUI\Page;
use ImTools\WebUI\Request;

require_once __DIR__ . '/../../../src/bootstrap.php';


if (!empty($_POST)) {
    if (!($id = Gallery::addImage($_POST))) {
        $template_vars['errors'] = 'Failed to add album';
    } else {
        Request::redirect('/gallery/image/?id=' . $id);
    }
}

$template_vars['gallery_menu'] = Page::get(null, 'gallery-pages');
$template_vars['page'] = Page::get('image-add', 'gallery-pages');
Template::display('gallery/image-add.twig', $template_vars);
