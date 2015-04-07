<?php
namespace ImTools\WebUI;

class Api
{
    // Upon adding new action don't forget to update getAllDigests() method
    /// Adds thumbnail by path on FS
    const ACT_ADD_THUMB = 'add_thumb';

    public static function generateDigest($action)
    {
        if (! ($key = Conf::get('api', 'key'))) {
            throw new \RuntimeException("API key configuration is wrong");
        }

        return strtolower(sha1($key . $action));
    }

    public static function checkDigest($digest, $action)
    {
        $true_digest = static::generateDigest($action);
        $result = (strtolower($digest) == $true_digest);
        if (!$result) {
            trigger_error("Digest mismatch: $digest != $true_digest, act: '$action'");
        }
        return $result;
    }

    public static function getAllDigests()
    {
        return [
            self::ACT_ADD_THUMB => static::generateDigest(self::ACT_ADD_THUMB),
        ];
    }
}
