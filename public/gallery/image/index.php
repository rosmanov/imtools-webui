<?php
use ImTools\WebUI\Template;
use ImTools\WebUI\Gallery;
use ImTools\WebUI\Page;
use ImTools\WebUI\Request;

require_once __DIR__ . '/../../../src/bootstrap.php';

if (! ($id = Request::getIntFromGet('id', 0, 0))) {
    die('Invalid image ID');
}

$template_vars['gallery_menu'] = Page::get(null, 'gallery-pages');
$template_vars['page'] = Page::get('gallery');
$template_vars['page']['name'] .= ' / Image';
$template_vars['image'] = Gallery::getImage($id);
Template::display('gallery/image-view.twig', $template_vars);
