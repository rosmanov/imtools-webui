<?php
namespace ImTools\WebUI\WSCommand;

class Diff extends \ImTools\WebUI\WSCommand
{
    protected
        $name = 'diff',
        $arguments;

    public function __construct(array $arguments)
    {
        foreach ($arguments as $k => $v) {
            switch ($k) {
                case 'old_image':
                case 'new_image':
                case 'out_image':
                    $this->arguments[$k] = $v;
                    break;
                default:
                    throw new \BadMethodCallException("Unknown argument name '$k'");
            }

            $a = &$this->arguments;
        }

        if (! isset($a['old_image'])
            || !isset($a['new_image'])
            || !isset($a['out_image']))
        {
            throw new \BadMethodCallException('Invalid input: ' . var_export($a, true));
        }
    }

    public function generateDigest()
    {
        $config = $this->getConfig();

        return sha1($config['application']
            . $this->arguments['old_image']
            . $this->arguments['new_image']
            . $config['key']);
    }
}
