<?php

//get extension of the file
function getFileExtension($file_name) {
    $filePart = explode('.', strtolower(basename($file_name)));
    return $filePart[count($filePart) - 1];
}

function createImage($name, $filename, $new_w, $new_h) {
    $system2 = explode('.', strtolower(basename($filename)));
    $system2[1] = $system2[1];

    $src_img = imagecreatefromstring(readFileData($name));

    $old_w = imageSX($src_img);
    $old_h = imageSY($src_img);


    $thumb_w = $new_w;
    $thumb_h = $new_h;

    if ($new_w > $old_w) {
        $thumb_w = $old_w;
        $thumb_h = $thumb_w / $old_w * $old_h;
    } else {
        $thumb_w = $new_w;
        $thumb_h = $thumb_w / $old_w * $old_h;
    }

    if ($thumb_h > $new_h) {
        $thumb_h = $new_h;
        $thumb_w = $thumb_h / $old_h * $old_w;
    }

    $dst_img = ImageCreateTrueColor($thumb_w, $thumb_h);
    imagealphablending($dst_img, false);
    imagesavealpha($dst_img, true);
    $transparent = imagecolorallocatealpha($dst_img, 255, 255, 255, 127);
    imagefilledrectangle($dst_img, 0, 0, $thumb_w, $thumb_h, $transparent);
    imagecopyresampled($dst_img, $src_img, 0, 0, 0, 0, $thumb_w, $thumb_h, $old_w, $old_h);

    if (preg_match("/png/", $system2[1])) {
        imagepng($dst_img, $filename);
    } else {
        imagejpeg($dst_img, $filename, 90);
    }
    imagedestroy($dst_img);
    imagedestroy($src_img);
}

function saveUploadedFile($name, $toPath, $width = 0, $height = 0) {
    if (!$_FILES[$name]) {
        return;
    }
    if ($_FILES[$name]["error"] != 0) {
        return;
    }

    $tempFile = $_FILES[$name]['tmp_name'];
    $fileName = $_FILES[$name]['name'];
    //$fileSize = $_FILES['Filedata']['size'];

    if (!is_dir(dirname($toPath))) {
        mkdir(dirname($toPath), true);
        chmod(dirname($toPath), 777);
    }

    if ($width == 0) {
        move_uploaded_file($tempFile, $toPath);
    } else {
        move_uploaded_file($tempFile, $tempFile . "." . getFileExtension($fileName));
        createImage($tempFile . "." . getFileExtension($fileName), $toPath, $width, $height);
    }
}
