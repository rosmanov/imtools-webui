<?php
namespace ImTools\WebUI;


abstract class WSCommand {
    protected
        /// WebSocket server application name
        $app_name,
        /// WebSocket server command name
        $name,
        $arguments;

    abstract public function __construct(array $arguments);
    abstract public function generateDigest();

    public function getName()
    {
        return $this->name;
    }

    protected function getConfig()
    {
        static $config;

        if ($config) {
            return $config;
        }

        if (! ($config = Conf::get('wsclient'))) {
            throw new \RuntimeException('failed to fetch wsclient config');
        }

        return $config;
    }
}
