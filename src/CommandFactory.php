<?php
namespace ImTools\WebUI;

abstract class CommandFactory {
    abstract protected function createCommand();

    public function requestCommand() {
        return $this->createCommand();
    }
}
