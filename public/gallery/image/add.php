<?php
use ImTools\WebUI\Gallery;
use ImTools\WebUI\Request;
use ImTools\WebUI\Response;

require_once __DIR__ . '/../../../src/bootstrap.php';

if (empty($_POST)) {
    return;
}

try {
    if ($image = Gallery::addImage()) {
        Response::jsonSuccess($image);
    } else {
        Response::jsonError("Failed to add image to gallery");
    }
} catch (BadMethodCallException $e) {
    Response::badRequest($e->getMessage());
} catch (Exception $e) {
    Response::jsonError($e->getMessage());
}
