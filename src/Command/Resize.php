<?php
namespace ImTools\WebUI\Command;


class Resize extends \ImTools\WebUI\Command {

    public function __construct(array $options) {
        $this->executable = 'imresize';

        foreach ($options as $k => $v) {
            $v = trim($v);

            switch ($k) {
            case 'source':
                if (!file_exists($v)) {
                    throw new \RuntimeException("File '$v' doesn't exist");
                }
                break;
            case 'output':
                if (!file_exists(dirname($v))) {
                    throw new \RuntimeException("Output directory doesn't exist for file: '$v'");
                }
                break;
            case 'width': // passthrough
            case 'height':
                $v = (int) $v;
                break;
            case 'fx': // passthrough
            case 'fy':
                $v = (double) $v;
                break;
            case 'interpolation':
                if (!$v) continue;
                break;
            default:
                throw new \BadMethodCallException("Unknown option: " . var_export($k, true));
            }

            $this->options[$this->getOptionKey($k)] = $v;
        }
    }

    public function run() {
        return $this->exec();
    }
}
