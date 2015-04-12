<?php
namespace ImTools\WebUI;

abstract class CommandFactory {
    const
        CMD_VERSION = 'Version',
        CMD_RESIZE  = 'Resize',
        CMD_MERGE   = 'Merge',
        CMD_DIFF    = 'Diff';

    abstract function __construct();

    public static function create($type, array $options) {
        switch ($type) {
            case self::CMD_VERSION: // no break
            case self::CMD_RESIZE: // no break
            case self::CMD_MERGE: // no break
            case self::CMD_DIFF: // no break
                $class = __NAMESPACE__ . '\\Command\\' . $type;
                break;
            default:
                throw new \BadMethodCallException('Unknown type: '
                    . var_export($type, true));
        }

        return new $class($options);
    }
}
