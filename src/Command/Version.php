<?php
namespace ImTools\WebUI\Command;


class Version extends \ImTools\WebUI\Command {

    public function __construct(array $options) {
        $this->executable = 'imresize';
        $this->options = [
            '--version' => null,
        ];
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
