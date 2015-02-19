<?php
use ImTools\WebUI\Template;
use ImTools\WebUI\Gallery;
use ImTools\WebUI\Request;
use ImTools\WebUI\Page;

require_once __DIR__ . '/../../src/bootstrap.php';

$limit = Request::getIntFromGet('limit', 5, 0, 100);
$p = Request::getIntFromGet('p');
$template_vars['albums'] = Gallery::getAlbumList($p, $limit);
$n_albums = $template_vars['albums'] ? Gallery::countAlbums() : 0;
$template_vars['num_pages'] = ceil($n_albums / $limit);

$template_vars['page'] = Page::get('gallery');

Template::display('gallery/index.twig', $template_vars);