<?php
declare(strict_types=1);
session_start();
$config = require_once 'config.php';
$uploadsRaw = $config['uploads_raw'];
$uploads = $config['uploads'];

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: no-referrer');

function fail(string $msg, int $code = 400): never {
    http_response_code($code);
    echo "<p class='err'>" . htmlspecialchars($msg, ENT_QUOTES) . "</p>";
    exit;
}

if(!isset($_POST['csrf'], $_SESSION['csrf']) || !hash_equals($_SESSION['csrf'],(string) $_POST['csrf'])) {
    fail('Invalid CSRF token', 403);
}

if(!isset($_FILES['avatar'])) {
    fail('No file uploaded', 400);
}
$f = $_FILES['avatar'];

if($f['error'] !== UPLOAD_ERR_OK) {
    $errors = [
        UPLOAD_ERR_INI_SIZE => 'File too large (max 5MB)',
        UPLOAD_ERR_FORM_SIZE => 'File too large (max 5MB)',
        UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
        UPLOAD_ERR_NO_FILE => 'No file was uploaded',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
        UPLOAD_ERR_EXTENSION => 'File upload stopped by extension',
    ];
    fail($errors[$f['error']] ?? 'Unknown upload error');
}

if($f['size'] <= 0 || $f['size'] > $config['max_bytes']) {
    fail('File size is invalid or exceeds 5MB limit');
}

$rawName = bin2hex((random_bytes(16)));
$tmpRawPath = $uploadsRaw . DIRECTORY_SEPARATOR . $rawName;

if(!move_uploaded_file($f['tmp_name'], $tmpRawPath)) {
    fail('Failed to save/move uploaded file');
}
@chmod($tmpRawPath, 0640);

$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->file($tmpRawPath) ?: 'application/octet-stream';
if(!in_array($mime, $config['allowed_mimes'], true)) {
    @unlink($tmpRawPath);
    fail('Invalid file type. Only JPG, PNG, and WebP are allowed.');
}

[$width, $height, $imgType] = getimagesize($tmpRawPath) ?: [0,0,0];

switch ($mime) {
    case 'image/jpeg':
        $src = imagecreatefromjpeg($tmpRawPath);
        break;
    case 'image/png':
        $src = imagecreatefrompng($tmpRawPath);
        break;
    case 'image/webp':
        $src = imagecreatefromwebp($tmpRawPath);
        break;
    default:
        @unlink($tmpRawPath);
        fail('Unsupported image type/format');
}

if(!$src) {
    @unlink($tmpRawPath);
    fail('Failed to decode image');
}

if($mime  === 'image/jpeg' && function_exists(('exif_read_data')))  {
    $exif = @exif_read_data($tmpRawPath);
    $orientation = $exif['Orientation'] ?? 1;

    if($in_array($orientation, [3, 6, 8], true)) {
        switch ($orientation) {
            case 3: $src = imagerotate($src, 180, 0); break;
            case 6: $src = imagerotate($src, -90, 0); break;
            case 8: $src = imagerotate($src, 90, 0); break;
        }

        $width = imagesx($src);
        $height = imagesy($src);
    }
}

$maxW = (int)$config['max_width'];
$maxH = (int)$config['max_height'];
$scale = min(1.0, $maxW / $width, $maxH / $height);

$dst = $src;
if($scale < 1.0) {
    $newW = max(1, (int)floor($width * $scale));
    $newH = max(1, (int)floor($height * $scale));
    $dst = imagecreatetruecolor($newW, $newH);
    imagealphablending($dst, false);
    imagesavealpha($dst, true);
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $width, $height);
    imagedestroy($src);
}

$outputFormat = $config['output_format'];
$quality = (int)$config['quality'];
$ext = $outputFormat;

$finalName = bin2hex(random_bytes(18)) . '.' . $ext;
$finalPath = $uploads . DIRECTORY_SEPARATOR . $finalName;
$ok = false;

if($outputFormat === 'webp') {
    if(function_exists(('imagewebp'))) {
        $ok = imagewebp($dst, $finalPath, $quality);
    } elseif($outputFormat === 'jpeg' || $outputFormat === 'jpg') {
        $ok = imagejpeg($dst, $finalPath, $quality);
    } elseif($outputFormat === 'png') {
        $pngQ = (int)round((100 - max(0, min(100, $quality))) * 9 / 100);
        $ok = imagepng($dst, $finalPath, $pngQ);
    }
}

imagedestroy($dst);
@unlink($tmpRawPath);
if(!$ok) {
    @unlink($finalPath);
    fail('Failed to save the processed image');
}

@chmod($finalPath, 0640);
echo "<p class='ok'>Image uploaded successfully: <a href='" . htmlspecialchars($finalPath, ENT_QUOTES) . "'>" . htmlspecialchars($finalName, ENT_QUOTES) . "</a></p>";