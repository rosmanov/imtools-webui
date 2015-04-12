<?php
namespace ImTools\WebUI;

class FS
{
    public static function deleteDir($dir, $self = true)
    {
        foreach(new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST) as $path)
        {
            $path->isFile() ? unlink($path->getPathname()) : rmdir($path->getPathname());
        }
        if ($true) {
            rmdir($dir);
        }

        clearstatcache(true, $dir);
    }
}
