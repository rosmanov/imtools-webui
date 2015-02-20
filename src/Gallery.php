<?php
namespace ImTools\WebUI;

class Gallery
{
    const
        ALBUMS_TABLE = 'albums',
        IMAGES_TABLE = 'images';

    public static function getAlbumList($start = 0, $limit = 100)
    {
        $start = (int) $start;
        $limit = (int) $limit;

        return Db::fetchAll("SELECT id, name FROM " . static::ALBUMS_TABLE. " LIMIT $start, $limit");
    }

    public static function countAlbums()
    {
        return (int) Db::f1('SELECT COUNT(*) FROM ' . static::ALBUMS_TABLE);
    }

    public static function addAlbum(array $data)
    {
        if (! ($name = trim($data['name']))) {
            throw new RuntimeException('Album name cannot be empty');
        }

        return Db::insert(self::ALBUMS_TABLE, [
            'name' => $name,
        ]);
    }

    public static function addImage(array $data)
    {
        if (! ($name = trim($data['name']))) {
            throw new RuntimeException('Image name cannot be empty');
        }
        // XXX handle uploading
    }

    public static function getAlbum($album_id)
    {
        $album_id = (int) $album_id;

        return Db::fetch("SELECT * FROM " . static::ALBUMS_TABLE . " WHERE id = $album_id");
    }

    public static function getImages($album_id)
    {
        $album_id = (int) $album_id;

        return Db::fetch("SELECT * FROM " . static::IMAGES_TABLE
            . " WHERE id = $album_id
            ORDER BY created");
    }
}
