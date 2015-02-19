<?php
use ImTools\WebUI\Template;
use ImTools\WebUI\Gallery;
use ImTools\WebUI\Request;
use ImTools\WebUI\Page;

require_once __DIR__ . '/../../../src/bootstrap.php';

if (!($id = Request::getIntFromGet('id', 0, 0))) {
    die('Invalid album ID');
}


