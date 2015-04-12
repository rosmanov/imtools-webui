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

        $list = Db::fetchAll("SELECT id, name, interpolation, format_id FROM "
            . static::ALBUMS_TABLE. " LIMIT $start, $limit");
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
            throw new \RuntimeException('Album name cannot be empty');
        }

        if (! isset($data['format_id'])
            || ! in_array($data['format_id'], array_keys(Conf::get('thumbs'))))
        {
            throw new \RuntimeException('Invalid format id: '
                . var_export($data['format_id'], true));
        }
        $format_id = (int) $data['format_id'];

        return Db::insert(self::ALBUMS_TABLE, [
            'name'      => $name,
            'format_id' => $format_id,
            'created'   => null,
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
        if (! ($album_id = Request::getInt('album_id', 0))) {
            throw new \BadMethodCallException('Invalid album ID passed');
        }

        // 'image_id' > 0 means update. Otherwise we're uploading new image
        $image_id = Request::getInt('id', 0);

        $target_dir = Conf::get('fs', 'upload_dir');
        $target_basename = preg_replace('/(?:\-\-)+/', '-', preg_replace('/[^A-Za-z0-9\-\_\^\$\.\|]/', '-',
            mb_strtolower(basename($_FILES["file"]["name"]), 'UTF-8')));
        $target_file = $target_dir . '/' . $target_basename;
        $extension = pathinfo($target_file, PATHINFO_EXTENSION);

        $image_size = getimagesize($_FILES["file"]["tmp_name"]);
        if ($image_size === false) {
            throw new \BadMethodCallException('File is not an image');
        }

        $target_file = self::_makeUniqueFilename($target_file);
        if (file_exists($target_file)) {
            throw new \RuntimeException('Sorry, file already exists. Failed to pick unique filename.');
        }

        $allowed_extensions = ['jpg', 'png', 'jpeg', 'gif', 'bmp'];
        if (! in_array($extension, $allowed_extensions)) {
            throw new \BadMethodCallException('Sorry, only ' . implode(', ', $allowed_extensions)
                . ' files are allowed.');
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
        if ($image_id) {
            self::deleteImage($image_id);
            $image['id'] = $image_id;
        }
        if (! ($image['id'] = Db::insert(self::IMAGES_TABLE, $image))) {
            Db::rollback();
            throw \RuntimeException("Failed to insert image");
        }

        if (!isset($_POST['make_thumbnails'])) {
            return $image;
        }

        try {
            static::makeThumbnails($image['id']);
        } catch (\Exception $e) {
            if (!$image_id) {
                static::deleteImage($image['id']);
            }
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
        if ($image_id <= 0) {
            throw new \RuntimeException("Invalid image ID");
        }

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

        Db::query($q = "DELETE FROM " . static::IMAGES_TABLE . " WHERE id = $image_id");
        Db::query($q = "DELETE FROM " . static::THUMBS_TABLE . " WHERE image_id = $image_id");
    }

    protected static function createResizeCommandOptions(array $image, array $album, array $format)
    {
        $image_path = Conf::get('fs', 'upload_dir') . '/' . $image['filename'];

        $options = [
            'source'        => $image_path,
            'output'        => static::getThumbnailPath($image['id'], $format['id']),
            'interpolation' => $album['interpolation'],
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

        return $options;
    }

    public static function makeThumbnails($image_id, $format_id = null)
    {
        if (! ($image = static::getImage($image_id))) {
            throw new \RuntimeException("Image #$image_id not found");
        }
        if (! ($album = static::getAlbum($image['album_id']))) {
            throw new \RuntimeException("Failed to fetch album for image #$image_id");
        }

        $upload_dir = Conf::get('fs', 'upload_dir');
        $image_path = $upload_dir . '/' . $image['filename'];

        if (! file_exists($image_path)) {
            throw new \RuntimeException("image '$image_path' doesn't exist");
        }

        if ($format_id) {
            $formats = [$format_id => Conf::get('thumbs', $format_id)];
        } else {
            $formats = Conf::get('thumbs');
        }

        foreach ($formats as $format_id => $format) {
            $options = static::createResizeCommandOptions($image, $album, $format);
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


    public static function addThumbnail($path, $image_id, $format_id)
    {
        $image_id  = (int) $image_id;
        $format_id = (int) $format_id;

        if (! ($format = Conf::get('thumbs', $format_id))) {
            throw new \BadMethodCallException('Invalid format ID: ' . var_export($format_id, true));
        }

        if (! file_exists($path)) {
            throw new \BadMethodCallException("File '$path' doesn't exist");
        }

        $fields = [
            'image_id'  => $image_id,
            'filename'  => basename($path),
            'format_id' => $format_id,
        ];
        if (null === Db::insert(self::THUMBS_TABLE, $fields)) {
            throw new \RuntimeException('Failed to insert thumbnail into database, fields: '
                . var_export($fields, true));
        }

        return true;
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

    private static function _getImagesQuery(array $data)
    {
        $q_where = '';
        if (isset($data['album_id'])) {
            $q_where []= 'i.album_id = ' . (int) $data['album_id'];
        }
        if (isset($data['image_ids'])) {
            $q_where []= 'i.id IN(' . implode(',', array_map('intval', $data['image_ids'])) . ')';
        }

        return 'SELECT i.*, t.filename AS thumbnail
            FROM ' . static::IMAGES_TABLE . ' i
            JOIN ' . static::ALBUMS_TABLE . ' a ON a.id = i.album_id
            LEFT JOIN ' . static::THUMBS_TABLE . ' t ON t.image_id = i.id AND t.format_id = a.format_id
            WHERE ' . implode(' AND ', $q_where) . ' ORDER BY i.created, i.id';
    }

    public static function getImagesByIds(array $ids)
    {
        return Db::fetchAll(self::_getImagesQuery(['image_ids' => $ids]));
    }

    public static function getImages($album_id, $wscommand_info = false)
    {
        $album_id = (int) $album_id;

        $images = Db::fetchAll(self::_getImagesQuery(['album_id' => $album_id]));

        if (!$wscommand_info) {
            return $images;
        }

        if (! ($album = static::getAlbum($album_id))) {
            throw new \RuntimeException("Failed to fetch album #$album_id");
        }

        foreach ($images as &$i) {
            if ($i['thumbnail']) continue;

            $arguments = self::createResizeCommandOptions($i, $album, Conf::get('thumbs', $album['format_id']));
            $ws_command = WSCommandFactory::create(WSCommandFactory::CMD_RESIZE, $arguments);

            $i['wscmd'] = json_encode([
                'command'   => 'resize',
                'arguments' => $arguments,
                'digest'    => $ws_command->generateDigest(),
            ]);
        }

        return $images;
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

    public static function getImage($id, $thumbnail = null, $wscommand_info = false)
    {
        $id = (int) $id;

        if (!$thumbnail) {
            return Db::fetch("SELECT * FROM " . static::IMAGES_TABLE . " WHERE id = $id");
        }

        if (is_numeric($thumbnail)) {
            $thumbnail = (int) $thumbnail;
            $q_join = " LEFT JOIN " . static::THUMBS_TABLE . " t ON t.image_id = i.id AND t.format_id = $thumbnail";
        } else {
            $q_join = " JOIN " . static::ALBUMS_TABLE . " a ON a.id = i.album_id
                LEFT JOIN " . static::THUMBS_TABLE . " t ON t.image_id = i.id AND t.format_id = a.format_id ";
        }

        $image = Db::fetch("SELECT i.*, t.filename AS thumbnail
            FROM " . static::IMAGES_TABLE . " i $q_join WHERE i.id = $id");

        if (!$image['thumbnail'] && $wscommand_info) {
            $album = self::getAlbum($image['album_id']);
            $format = Conf::get('thumbs', (is_int($thumbnail) ? $thumbnail : $album['format_id']));
            $arguments = self::createResizeCommandOptions($image, $album, $format);
            $ws_command = WSCommandFactory::create(WSCommandFactory::CMD_RESIZE, $arguments);
            $image['wscmd'] = json_encode([
                'command'   => 'resize',
                'arguments' => $arguments,
                'digest'    => $ws_command->generateDigest(),
            ]);
        }

        return $image;
    }

    public static function getImageFilenamesHash($album_id)
    {
        $album_id = (int) $album_id;

        return Db::fetchHash("SELECT id, filename FROM " . static::IMAGES_TABLE
            . " WHERE album_id = $album_id");
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
