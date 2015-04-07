<?php
namespace ImTools\WebUI\WSCommand;

class Meta extends \ImTools\WebUI\WSCommand
{
    protected
        $name = 'meta',
        $arguments;

    public function __construct(array $arguments)
    {
        foreach ($arguments as $k => $v) {
            switch ($k) {
                case 'subcommand':
                    switch ($v) {
                        case 'all': // no break
                        case 'version': // no break
                        case 'features': // no break
                        case 'copyright': // no break
                            $this->arguments[$k] = $v;
                            break;
                        default:
                            throw new \BadMethodCallException("Unknown subcommand '$v'");
                    }
                    break;
                default:
                    throw new \BadMethodCallException("Unknown argument name '$k'");
            }

            if (!isset($this->arguments['subcommand'])) {
                $this->arguments['subcommand'] = 'all';
            }
        }
    }

    public function generateDigest()
    {
        $config = $this->getConfig();

        return sha1($config['application']
            . $this->arguments['subcommand']
            . $config['key']);
    }
}
