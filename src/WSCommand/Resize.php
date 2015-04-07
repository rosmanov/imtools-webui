<?php
namespace ImTools\WebUI\WSCommand;

class Resize extends \ImTools\WebUI\WSCommand
{
    protected
        $name = 'resize',
        $arguments;

    public function __construct(array $arguments)
    {
        foreach ($arguments as $k => $v) {
            switch ($k) {
                case 'source': // no break
                case 'output': // no break
                case 'width': // no break
                case 'height': // no break
                case 'fx': // no break
                case 'fy': // no break
                case 'interpolation':
                    $this->arguments[$k] = $v;
                    break;
                default:
                    throw new \BadMethodCallException("Unknown argument name '$k'");
            }

        }

        $a = &$this->arguments;

        if (!isset($a['source'])
            || !isset($a['output'])
            ||
            !(
                (isset($a['width']) && isset($a['height']))
                ||
                (isset($a['fx']) && isset($a['fy']))
            )
        )
        {
            throw new \BadMethodCallException('Some of required arguments are not passed: '
                .var_export($a, true));
        }

        foreach (['width', 'height', 'fx', 'fy'] as $k) {
            if (!isset($a[$k])) {
                $a[$k] = 0;
            }
        }

    }

    public function generateDigest()
    {
        $config = $this->getConfig();

        $fx = $this->arguments['fx'];
        $fy = $this->arguments['fy'];

        $s = $config['application']
            . $this->arguments['source']
            . $this->arguments['output']
            . $this->arguments['width']
            . $this->arguments['height']
            . (round($fx * 1000) / 1000)
            . (round($fy * 1000) / 1000)
            . $config['key'];
        //trigger_error("Generating digest: sha1($s)");
        return sha1($s);
    }
}
