<?php
use ImTools\WebUI\Gallery;
use ImTools\WebUI\Request;

require_once __DIR__ . '/../../../src/bootstrap.php';

if (!($id = Request::getIntFromGet('id', 0, 0))) {
    die('Invalid album ID');
}

try {
    Gallery::deleteAlbum($id);
    Request::redirect('/gallery/');
} catch (Exception $e) {
    die($e->getMessage());
}
