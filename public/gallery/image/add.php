<?php
use ImTools\WebUI\Template;
use ImTools\WebUI\Gallery;
use ImTools\WebUI\Page;
use ImTools\WebUI\Request;
use ImTools\WebUI\Response;

require_once __DIR__ . '/../../../src/bootstrap.php';


if (!empty($_POST)) {
    try {
        if ($image = Gallery::addImage()) {
            echo '{"status":"success"}';
            //Request::redirect('/gallery/image/?id=' . $image['id']);
        } else {
            //echo '{"status":"error"}';
            Response::internalServerError("Failed to add image to gallery");
        }
    } catch (BadMethodCallException $e) {
        Response::badRequest($e->getMessage());
    } catch (Exception $e) {
        //$template_vars['errors'] = $e->getMessage();
        //echo '{"status":"error"}';
        Response::internalServerError($e->getMessage());
    }
    exit;
}

$template_vars['gallery_menu'] = Gallery::getMenu(Gallery::MENU_TYPE_IMAGE);
$template_vars['page'] = Page::get('image-add', 'album-pages');
Template::display('gallery/image-add.twig', $template_vars);
