<?php
namespace ImTools\WebUI;

class Gallery
{
    const ALBUMS_TABLE = 'albums';

    public static function getAlbumList($start = 0, $limit = 100)
    {
        $start = (int) $start;
        $limit = (int) $limit;

        return Db::fetchAll("SELECT id, name FROM albums LIMIT $start, $limit");
    }

    public static function countAlbums()
    {
        return (int) Db::f1('SELECT COUNT(*) FROM albums');
    }

    public static function addAlbum(array $data)
    {
        if (! ($name = trim($data['name']))) {
            throw new RuntimeException('Album name cannot be empty');
        }

        return Db::insert(ALBUMS_TABLE, [
            'name' => $name,
        ]);
    }
}
