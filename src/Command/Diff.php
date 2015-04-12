<?php
namespace ImTools\WebUI\Command;

class Diff extends ImTools\WebUI\Command {
    public function __construct(array $options) {
        $this->executable = 'imdiff';
    }

    public function run() {
    }
}
