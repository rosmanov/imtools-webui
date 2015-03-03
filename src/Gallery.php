<?php
namespace ImTools\WebUI;

class Gallery
{
    const
        ALBUMS_TABLE = 'albums',
        IMAGES_TABLE = 'images',
        THUMBS_TABLE = 'thumbs',
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
            'name'    => $name,
            'created' => null,
        ]);
    }

    private static function _makeUniqueFilename($filename)
    {
        $pathinfo = pathinfo($filename);

        for ($i = 0; file_exists($filename); ++$i) {
            if ($i > 100) {
                $i = uniqid('dup');
            }
            $filename = $pathinfo['dirname'] . '/'
                . $pathinfo['filename'] . '_' . $i . '.' . $pathinfo['extension'];
        }

        return $filename;
    }

    public static function addImage()
    {
        if (! ($album_id = Request::getIntFromPost('album_id', 0))) {
            throw new \BadMethodCallException('Invalid album ID passed');
        }

        $target_dir = Conf::get('fs', 'upload_dir');
        $target_file = $target_dir . '/' . basename($_FILES["file"]["name"]);
        $image_file_type = pathinfo($target_file, PATHINFO_EXTENSION);

        $image_size = getimagesize($_FILES["file"]["tmp_name"]);
        if ($image_size === false) {
            throw new \BadMethodCallException('File is not an image');
        }

        $target_file = self::_makeUniqueFilename($target_file);
        if (file_exists($target_file)) {
            throw new \RuntimeException('Sorry, file already exists. Failed to pick unique filename.');
        }

        if ($image_file_type != "jpg" && $image_file_type != "png" && $image_file_type != "jpeg"
            && $image_file_type != "gif" )
        {
            throw new \BadMethodCallException('Sorry, only JPG, JPEG, PNG & GIF files are allowed.');
        }

        if (! move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
            throw new \RuntimeException("Sorry, there was an error uploading your file.");
        }

        $filename = basename($target_file);
        $image = [
            'album_id'      => $album_id,
            'filename'      => $filename,
            'filename_hash' => sha1($filename),
            'width'         => $image_size[0],
            'height'        => $image_size[1],
            'created'       => null,
        ];
        $image['id'] = Db::insert(self::IMAGES_TABLE, $image);

        try {
            static::makeThumbnails($image['id']);
        } catch (\Exception $e) {
            static::deleteImage($image['id']);
            throw $e;
        }

        return $image;
    }


    public static function deleteAlbum($album_id)
    {
        $album_id = (int) $album_id;

        if ($image_ids = static::getImageIds($album_id)) {
            foreach ($image_ids as $image_id) {
                static::deleteImage($image_id);
            }
        }

        Db::query("DELETE FROM " . static::ALBUMS_TABLE . " WHERE id = $album_id");
    }

    public static function deleteImage($image_id)
    {
        $image_id = (int) $image_id;

        $cwd = getcwd();
        chdir(Conf::get('fs', 'upload_dir'));

        if ($image = static::getImage($image_id)) {
            unlink($image['filename']);
        }

        if ($thumbs = static::getThumbnails($image_id)) {
            foreach ($thumbs as $t) {
                unlink($t['filename']);
            }
        }

        chdir($cwd);

        Db::query("DELETE FROM " . static::IMAGES_TABLE . " WHERE id = $image_id");
        Db::query("DELETE FROM " . static::THUMBS_TABLE . " WHERE image_id = $image_id");
    }

    protected static function makeThumbnails($image_id)
    {
        if (! ($image = static::getImage($image_id))) {
            throw new \RuntimeException("Image #$image_id not found");
        }

        $upload_dir = Conf::get('fs', 'upload_dir');
        $image_path = $upload_dir . '/' . $image['filename'];

        if (! file_exists($image_path)) {
            throw new \RuntimeException("image '$image_path' doesn't exist");
        }

        foreach (Conf::get('thumbs') as $format_id => $format) {
            $options = [
                'source' => $image_path,
                'output' => static::getThumbnailPath($image_id, $format_id),
            ];

            $fx = $format['width'] / $image['width'];
            $fy = $format['height'] / $image['height'];
            if ($fx != $fy) {
                $options['fx'] = $fx;
                //$options['fy'] = $fy;
                // Make proportional height
                $options['fy'] = $fx;
            } else { // non-proportional format
                $options['width']  = $format['width'];
                $options['height'] = $format['height'];
            }

            $commands[$format_id] = CommandFactory::create(CommandFactory::CMD_RESIZE, $options);
        }

        if (! Db::begin()) {
            throw new \RuntimeException("Failed to begin transaction");
        }

        try {
            foreach ($commands as $format_id => $c) {
                $c->run();

                if (! ($filename = $c->getOption('output'))) {
                    throw new \LogicException("Failed to fetch output filename");
                }

                $fields = [
                    'image_id'  => $image_id,
                    'filename'  => basename($filename),
                    'format_id' => $format_id,
                ];
                if (null === Db::insert(self::THUMBS_TABLE, $fields)) {
                    throw new \RuntimeException('Failed to insert thumbnail into database, fields: '
                        . var_export($fields, true));
                }
            }
        } catch (\Exception $e) {
            Db::rollback();
            trigger_error($e->getMessage(), E_USER_WARNING);
            throw $e;
            return false;
        }

        return Db::commit();
    }

    protected static function getThumbnailPath($image_id, $format_id)
    {
        return Conf::get('fs', 'upload_dir') . '/' . $image_id . '_' . $format_id . '.png';
    }

    public static function getAlbum($album_id)
    {
        $album_id = (int) $album_id;

        return Db::fetch('SELECT * FROM ' . static::ALBUMS_TABLE . " WHERE id = $album_id");
    }

    public static function getImages($album_id)
    {
        $album_id = (int) $album_id;

        return Db::fetchAll('SELECT i.*, t.filename AS thumbnail FROM ' . static::IMAGES_TABLE . " i
            JOIN " . static::ALBUMS_TABLE . " a ON a.id = i.album_id
            JOIN " . static::THUMBS_TABLE . " t ON t.image_id = i.id
            WHERE i.album_id = $album_id AND t.format_id = a.format_id
            ORDER BY i.created");
    }

    public static function getImageIds($album_id)
    {
        $album_id = (int) $album_id;

        return Db::fetchCol('SELECT id FROM ' . static::IMAGES_TABLE
            . " WHERE album_id = $album_id");
    }

    public static function getThumbnails($image_id)
    {
        $image_id = (int) $image_id;

        return Db::fetchAll('SELECT * FROM ' . static::THUMBS_TABLE
            . " WHERE image_id = $image_id");
    }

    public static function getThumbnail($image_id, $format_id)
    {
        $image_id  = (int) $image_id;
        $format_id = (int) $format_id;

        return Db::fetch('SELECT * FROM ' . static::THUMBS_TABLE
            . " WHERE image_id = $image_id AND format_id = $format_id");
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
