<?php
use ImTools\WebUI\Gallery;
use ImTools\WebUI\Request;
use ImTools\WebUI\Response;
use ImTools\WebUI\Conf;
use ImTools\WebUI\Api;

require_once __DIR__ . '/../src/bootstrap.php';

$action = isset($_GET['action']) ? $_GET['action'] : null;
$digest = isset($_GET['digest']) ? $_GET['digest'] : null;

if (!$digest) {
    Response::jsonError('Digest is empty');
}
if (! Api::checkDigest($digest, $action)) {
    Response::jsonError('Invalid digest');
}

switch ($action) {
case Api::ACT_ADD_THUMB:
    if (! ($path = isset($_GET['path']) ? $_GET['path'] : null)) {
        Response::jsonError('Invalid path');
    }
    if (! ($image_id = Request::getIntFromGet('image-id', 0))) {
        Response::jsonError('Invalid image ID');
    }

    $formats = Conf::get('thumbs');
    $format_id = Request::getIntFromGet('format-id', 0);
    if (! ($format_id && isset($formats[$format_id]))) {
        Response::jsonError('Invalid format ID');
    }

    try {
        if (! Gallery::addThumbnail($path, $image_id, $format_id)) {
            Response::jsonError('Failed');
        }
    } catch (\Exception $e) {
        Response::jsonError($e->getMessage());
    }

    Response::jsonSuccess();
    break;

default:
    Response::jsonError('Unknown action');
}
