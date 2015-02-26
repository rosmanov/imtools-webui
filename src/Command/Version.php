<?php
namespace ImTools\WebUI\Command;

class Version extends \ImTools\WebUI\Command {
    protected $executable = 'imresize';
    protected $options = ['--version' => null];

    public function __construct() {
    }

    public function run() {
        $result = [];

        $a = explode(PHP_EOL, $this->exec());
        foreach ($a as $line) {
            if (!preg_match('/^([A-Z]\w+)\s*\:\s*(.*)$/', $line, $m) || count($m) != 3) {
                continue;
            }

            $result[trim($m[1])] = trim($m[2]);
        }

        return $result;
    }
}
