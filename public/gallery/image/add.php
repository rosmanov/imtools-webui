<?php
use ImTools\WebUI\Template;
use ImTools\WebUI\Gallery;
use ImTools\WebUI\Page;
use ImTools\WebUI\Request;

require_once __DIR__ . '/../../../src/bootstrap.php';


if (!empty($_POST['submit'])) {
    try {
        if ($image = Gallery::addImage()) {
            Request::redirect('/gallery/image/?id=' . $image['id']);
        }
    } catch (Exception $e) {
        $template_vars['errors'] = $e->getMessage();
    }
}

$template_vars['gallery_menu'] = array_merge(Page::get(null, 'gallery-pages'), Page::get(null, 'album-pages'));
$template_vars['page'] = Page::get('image-add', 'album-pages');
Template::display('gallery/image-add.twig', $template_vars);
