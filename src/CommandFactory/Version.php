<?php
namespace ImTools\WebUI\CommandFactory;

class Version extends \ImTools\WebUI\CommandFactory {
    public function __construct() {}
    protected function createCommand() {
        return new \ImTools\WebUI\Command\Version();
    }
}
