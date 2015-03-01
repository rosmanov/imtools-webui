<?php
use ImTools\WebUI\Gallery;
use ImTools\WebUI\Request;

require_once __DIR__ . '/../../../src/bootstrap.php';

if (!($id = Request::getIntFromGet('id', 0, 0))) {
    die('Invalid image ID');
}

try {
    if (! ($image = Gallery::getImage($id))) {
        throw new RuntimeException("Image not found");
    }
    Gallery::deleteImage($id);
    Request::redirect('/gallery/album/?id=' . $image['album_id']);
} catch (Exception $e) {
    die($e->getMessage());
}
