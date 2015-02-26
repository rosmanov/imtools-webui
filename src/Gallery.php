<?php
namespace ImTools\WebUI;

class Gallery
{
    const
        ALBUMS_TABLE = 'albums',
        IMAGES_TABLE = 'images',
        MENU_TYPE_ALBUM = 1,
        MENU_TYPE_IMAGE = 2;


    public static function getAlbumList($start = 0, $limit = 100)
    {
        $start = (int) $start;
        $limit = (int) $limit;

        $list = Db::fetchAll("SELECT id, name FROM " . static::ALBUMS_TABLE. " LIMIT $start, $limit");
        foreach ($list as &$l) {
            $l['n_images'] = self::countImages($l['id']);
        }
        return $list;
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

    public static function addImage()
    {
        Request::requirePostString('name', 'Image name cannot be empty');

        if (! ($album_id = Request::getIntFromPost('album_id', 0))) {
            throw new \RuntimeException('Invalid album ID passed');
        }

        $target_dir = Conf::get('fs', 'upload_dir');
        $target_file = $target_dir . '/' . basename($_FILES["file"]["name"]);
        $image_file_type = pathinfo($target_file, PATHINFO_EXTENSION);

        if (isset($_POST['submit'])) {
            $check = getimagesize($_FILES["file"]["tmp_name"]);
            if ($check === false) {
                throw new \RuntimeException('File is not an image');
            }
        }

        if (file_exists($target_file)) {
            throw new \RuntimeException("Sorry, file already exists.");
        }

        if ($image_file_type != "jpg" && $image_file_type != "png" && $image_file_type != "jpeg"
            && $image_file_type != "gif" )
        {
            throw new \RuntimeException("Sorry, only JPG, JPEG, PNG & GIF files are allowed.");
        }

        if (! move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
            throw new \RuntimeException("Sorry, there was an error uploading your file.");
        }

        $filename = basename($target_file);
        $image = [
            'album_id'      => $album_id,
            'name'          => $_POST['name'],
            'filename'      => $filename,
            'filename_hash' => sha1($filename),
            'created'       => null,
        ];
        $image['id'] = Db::insert(self::IMAGES_TABLE, $image);

        return $image;
    }

    public static function getAlbum($album_id)
    {
        $album_id = (int) $album_id;

        return Db::fetch("SELECT * FROM " . static::ALBUMS_TABLE . " WHERE id = $album_id");
    }

    public static function getImages($album_id)
    {
        $album_id = (int) $album_id;

        return Db::fetchAll("SELECT * FROM " . static::IMAGES_TABLE
            . " WHERE album_id = $album_id
            ORDER BY created");
    }

    public static function getImage($id)
    {
        $id = (int) $id;

        return Db::fetch("SELECT * FROM " . static::IMAGES_TABLE . " WHERE id = $id");
    }

    public static function getMenu($menu_type) {
        switch ($menu_type) {
            case static::MENU_TYPE_ALBUM:
                $url_query = '?' . http_build_query([
                    'album_id' => Request::getIntFromGet('id', 0, 0),
                ]);
                $menu       = Page::get(null, 'gallery-pages');
                $album_menu = Page::get(null, 'album-pages');
                foreach ($album_menu as &$v) {
                    $v['url'] .=  $url_query;
                }
                return array_merge($menu, $album_menu);
            case static::MENU_TYPE_IMAGE:
                $url_query = '?' . http_build_query([
                    'id'       => Request::getIntFromGet('id',       0, 0),
                    'album_id' => Request::getIntFromGet('album_id', 0, 0),
                ]);
                $menu = Page::get(null, 'gallery-pages');
                $album_menu = Page::get(null, 'album-pages');
                foreach ($album_menu as &$v) {
                    $v['url'] .=  $url_query;
                }
                return array_merge($menu, $album_menu);
            default:
                throw new \BadMethodCallException("Unhandled menu type " . var_export($menu_type, true));
        }
    }

    public static function countImages($album_id)
    {
        $album_id = (int) $album_id;

        return Db::f1("SELECT COUNT(*) FROM images WHERE album_id = $album_id");
    }
}
