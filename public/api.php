<?php
use ImTools\WebUI\Gallery;
use ImTools\WebUI\Request;
use ImTools\WebUI\Response;
use ImTools\WebUI\Template;
use ImTools\WebUI\Conf;
use ImTools\WebUI\WSCommandFactory;
use ImTools\WebUI\Operation;

require_once __DIR__ . '/../src/bootstrap.php';

$action = isset($_GET['action']) ? $_GET['action'] : null;

switch ($action) {
case 'add_thumb':
    if (! ($path = isset($_GET['path']) ? $_GET['path'] : null)) {
        Response::jsonError('Invalid path');
    }
    if (! ($image_id = Request::getIntFromGet('image-id', 0))) {
        Response::jsonError('Invalid image ID');
    }

    $formats = Conf::get('thumbs');
    $format_id = Request::getIntFromGet('format-id', 0);
    if (! ($format_id && isset($formats[$format_id]))) {
        Response::jsonError('Invalid format ID');
    }

    try {
        if (! Gallery::addThumbnail($path, $image_id, $format_id)) {
            Response::jsonError('Failed');
        }
    } catch (\Exception $e) {
        Response::jsonError($e->getMessage());
    }

    Response::jsonSuccess();
    break;
case 'get_image_list_item':
    if (! ($image_id = Request::getIntFromGet('image-id', 0))) {
        Response::jsonError('Invalid image ID');
    }

    $image = Gallery::getImage($image_id, true);
    $template_vars['image'] = $image;
    $template_vars['album'] = Gallery::getAlbum($image['album_id']);
    $template_vars['thumbnail_formats'] = Conf::get('thumbs');
    Response::jsonSuccess(Template::render('gallery/image-list-item.twig', $template_vars));
    break;

case 'get_image_update_info':
    if (! ($image_id = Request::getIntFromGet('image-id', 0))) {
        Response::jsonError('Invalid image ID');
    }
    $format_id = Request::getIntFromGet('format-id', 2);

    $template_vars['image'] = Gallery::getImage($image_id, $format_id, true);
    Response::jsonSuccess(Template::render('gallery/image-update-info.twig', $template_vars));
    break;

case 'op_get_merge_preview':
    if (!isset($_POST['image_ids'])) {
        Response::jsonError('empty image ids');
    }
    $image_ids = array_map('intval', explode(',', $_POST['image_ids']));
    if (! ($images = Gallery::getImagesByIds($image_ids))) {
        Response::jsonError('no images found');
    }

    $template_vars['preview_dir'] = isset($_POST['preview_dir']) ? $_POST['preview_dir'] : '';

    // Generate diff commands
    try {
        $preview_dir = Conf::get('fs', 'tmp_dir') . '/' . $template_vars['preview_dir'];
        $upload_dir = Conf::get('fs', 'upload_dir');
        $preview_web_dir = '/tmp-uploads/' . $template_vars['preview_dir'];

        foreach ($images as &$image) {
            $ext = pathinfo($image['filename'], PATHINFO_EXTENSION);
            $i = 10;
            do {
                $tmp_file = tempnam($preview_dir, 'diff_');
                unlink($tmp_file);
                $dif_out_image = $preview_dir. '/' . basename($tmp_file) . '.' . $ext;
            } while (file_exists($dif_out_image) && --$i > 0);
            touch($dif_out_image);

            $diff_arguments = [
                'old_image' => $upload_dir . '/' . $image['filename'],
                'new_image' => $preview_dir . '/' . $image['filename'],
                'out_image' => $dif_out_image,
            ];

            $ws_diff_command = WSCommandFactory::create(WSCommandFactory::CMD_DIFF,
                $diff_arguments);

            $image['ws_diff_command'] = json_encode([
                'command'   => $ws_diff_command->getName(),
                'arguments' => $diff_arguments,
                'digest'    => $ws_diff_command->generateDigest(),
            ], \JSON_UNESCAPED_UNICODE);
            $image['diff_url'] = $preview_web_dir . '/' . basename($dif_out_image);

            $image['preview_url'] = $preview_web_dir . '/' . $image['filename'];
        }
    } catch (Exception $e) {
        Response::jsonError('Caught exception: ' . $e->getMessage());
    }

    $template_vars['images'] = $images;
    Response::jsonSuccess(Template::render('gallery/image-merge-preview.twig', $template_vars));
    break;

default:
    Response::jsonError('Unknown action');
}
