<?php
namespace ImTools\WebUI;

use \ImTools\WebUI\Exception\CommandFailedException;

abstract class Command {
    protected
        $executable,
        $options;

    abstract public function __construct();
    abstract public function run();

    protected function exec() {
        $command = $this->executable;
        if ($this->options) {
            foreach ($this->options as $k => $v) {
                $a []= $k . $v;
            }
            $command .= ' ' . implode(' ', $a);
            $a = null;
        }
        $descriptors = [
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
         ];
        $proc = proc_open($command, $descriptors, $pipes);
        if (!is_resource($proc)) {
            throw new \RuntimeException("Failed to create process for command: `$command'");
        }
        stream_set_blocking($pipes[2], 0);

        $stdout = stream_get_contents($pipes[1]);
        fclose($pipes[1]);

        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);

        if ($retval = proc_close($proc)) {
            throw new CommandFailedException($command . ': ' . $stderr, $retval);
        }

        return $stdout;
    }
}
