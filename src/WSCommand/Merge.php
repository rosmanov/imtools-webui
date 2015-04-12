<?php
namespace ImTools\WebUI\WSCommand;

class Merge extends \ImTools\WebUI\WSCommand
{
    protected
        $name = 'merge',
        $arguments;

    public function __construct(array $arguments)
    {
        foreach ($arguments as $k => $v) {
            switch ($k) {
                case 'old_image': // no break
                case 'new_image': // no break
                case 'input_images': // no break
                case 'output_images': // no break
                case 'strict': // no break
                    $this->arguments[$k] = $v;
                    break;
                default:
                    throw new \BadMethodCallException("Unknown argument name '$k'");
            }

        }

        $a = &$this->arguments;

        if (empty($a['old_image'])
            || empty($a['new_image'])
            || empty($a['input_images'])
            || empty($a['output_images'])
        )
        {
            throw new \BadMethodCallException('Some of required arguments are not passed: '
                .var_export($a, true));
        }

        foreach (['strict', 'fx', 'fy'] as $k) {
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
            . $this->arguments['old_image']
            . $this->arguments['new_image']
            . count($this->arguments['output_images'])
            . $this->arguments['strict']
            . $config['key'];
        return sha1($s);
    }
}
