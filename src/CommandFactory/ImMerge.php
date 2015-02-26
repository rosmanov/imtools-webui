<?php
namespace ImTools\WebUI\CommandFactory;

class ImMerge extends \ImTools\WebUI\CommandFactory {
    public function __construct() {}
    protected function createCommand() {
        return new \ImTools\WebUI\Command\ImMerge();
    }
}
