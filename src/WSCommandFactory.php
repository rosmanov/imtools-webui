<?php
namespace ImTools\WebUI;

abstract class WSCommandFactory {
    const
        CMD_META    = 'Meta',
        CMD_RESIZE  = 'Resize',
        CMD_MERGE   = 'Merge',
        CMD_DIFF    = 'Diff';

    abstract function __construct();

    public static function create($type, array $arguments) {
        switch ($type) {
            case self::CMD_META: // passthrough
            case self::CMD_RESIZE:
            case self::CMD_MERGE:
            case self::CMD_DIFF:
                $class = __NAMESPACE__ . '\\WSCommand\\' . $type;
                break;
            default:
                throw new \BadMethodCallException('Unknown type: '
                    . var_export($type, true));
        }

        return new $class($arguments);
    }
}
