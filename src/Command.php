<?php
namespace ImTools\WebUI;


use \ImTools\WebUI\Exception\CommandFailedException;


abstract class Command {
    protected
        $executable,
        $options;

    abstract public function __construct(array $options);

    abstract public function run();

    protected function getOptionKey($k)
    {
        return '--' . $k;
    }

    public function getOption($k)
    {
        $key = $this->getOptionKey($k);
        return isset($this->options[$key]) ? $this->options[$key] : null;
    }

    protected function exec()
    {
        $bin_dir = Conf::get('fs', 'imtools_bin_dir');
        $command = ($bin_dir ? $bin_dir . '/' : '') . $this->executable;
        if ($this->options) {
            foreach ($this->options as $k => $v) {
                if ($v !== null) {
                    $a []= $k . '=' . escapeshellarg($v);
                } else {
                    $a []= $k;
                }
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
