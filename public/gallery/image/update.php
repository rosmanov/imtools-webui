<?php
use ImTools\WebUI\Template;
use ImTools\WebUI\Gallery;
use ImTools\WebUI\Page;
use ImTools\WebUI\Request;
use ImTools\WebUI\Response;
use ImTools\WebUI\Conf;
use ImTools\WebUI\WSCommandFactory;

require_once __DIR__ . '/../../../src/bootstrap.php';


if (! ($image_id = Request::getInt('id', 0))) {
    Response::badRequest('Invalid image ID');
}
if (! ($image = Gallery::getImage($image_id, 2, true))) {
    Response::badRequest('No image found, image_id: ' . $image_id);
}
if (! ($album_id = Request::getInt('album_id', 0))) {
    Response::badRequest('Invalid album ID');
}

if (!empty($_POST)) {

    // Whether to merge difference between $image and the image being uploaded
    // to the album images
    if ($as_album_patch = !empty($_POST['as_album_patch'])) {
        // Save image file in a temporary directory, since the file will be
        // overwritten by the update

        $old_image = $image;

        // Create preview directory
        if (! ($tmp_dir = tempnam(Conf::get('fs', 'tmp_dir'), 'mtmp_'))) {
            Response::jsonError("Failed to create temp file");
        }
        unlink($tmp_dir);
        if (!mkdir($tmp_dir, 0755, true)) {
            Response::jsonError("Failed to create temp dir");
        }

        $upload_dir = Conf::get('fs', 'upload_dir');
        if (!copy($upload_dir . '/' . $image['filename'], $tmp_dir . '/' . $image['filename'])) {
            Response::jsonError('Failed to copy ' . $image['filename']
                . " from $upload_dir to $tmp_dir dir");
        }
    }

    try {
        $image = Gallery::addImage();
    } catch (Exception $e) {
        if (isset($tmp_dir)) FS::deleteDir($tmp_dir);
        Response::jsonError($e->getMessage());
    }
    if (!$as_album_patch) {
        Response::jsonSuccess(['image' => $image]);
    }

    // Merge difference between `$old_image` and `$image` into the rest of
    // album images

    $input_images_hash = Gallery::getImageFilenamesHash($album_id);
    unset($input_images_hash[$old_image['id']]);

    $output_images = array_map(function ($v) use ($tmp_dir) {
        return ($tmp_dir . '/' . $v);
    }, $input_images_hash);

    $input_images = array_map(function ($v) use ($upload_dir) {
        return ($upload_dir . '/' . $v);
    }, $input_images_hash);

    $preview_arguments = [
        'old_image'     => $tmp_dir        . '/' . $old_image['filename'],
        'new_image'     => $upload_dir     . '/' . $image['filename'],
        'input_images'  => $input_images,
        'output_images' => $output_images,
    ];
    if (isset($_POST['as_album_patch_strict'])) {
        $preview_arguments['strict'] = (int) abs($_POST['as_album_patch_strict']);
    }
    $real_merge_arguments                  = $preview_arguments;
    $real_merge_arguments['output_images'] = $preview_arguments['input_images'];

    try {
        $ws_preview_command    = WSCommandFactory::create(WSCommandFactory::CMD_MERGE, $preview_arguments);
        $ws_real_merge_command = WSCommandFactory::create(WSCommandFactory::CMD_MERGE, $real_merge_arguments);
    } catch (Exception $e) {
        if (isset($tmp_dir)) FS::deleteDir($tmp_dir);
        Response::jsonError($e->getMessage());
    }

    $preview_web_dir = preg_replace('/^'
        . preg_quote(Conf::get('fs', 'tmp_dir'), '/') . '\//', '', $tmp_dir);

    Response::jsonSuccess([
        'image_id'    => $image['id'],
        'preview_dir' => $preview_web_dir,
        'ws_preview_command' => [
            'command'   => $ws_preview_command->getName(),
            'arguments' => $preview_arguments,
            'digest'    => $ws_preview_command->generateDigest(),
        ],
        'ws_real_merge_command' => [
            'command'   => $ws_real_merge_command->getName(),
            'arguments' => $real_merge_arguments,
            'digest'    => $ws_real_merge_command->generateDigest(),
        ],
    ]);
}

$template_vars['gallery_menu'] = Gallery::getMenu(Gallery::MENU_TYPE_IMAGE);
$template_vars['page'] = Page::get('gallery');
$template_vars['page']['name'] .= ' / Image / Update';
$template_vars['image'] = $image;
Template::display('gallery/image-update.twig', $template_vars);
