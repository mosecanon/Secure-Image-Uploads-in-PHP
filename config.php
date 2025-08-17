<?php
declare(strict_types=1);

$ROOT = __DIR__;
$STORAGE = $ROOT . DIRECTORY_SEPARATOR . 'storage';
$UPLOADS_RAW = $STORAGE . DIRECTORY_SEPARATOR . 'uploads_raw';
$UPLOADS = $STORAGE . DIRECTORY_SEPARATOR . 'uploads';

foreach([$STORAGE, $UPLOADS_RAW, $UPLOADS] as $dir ) {
    if(!is_dir($dir) && !mkdir($dir, 0750, true)) {
        throw new RuntimeException("Failed to create directory: $dir");
    }
}

return [
    'max_bytes' => 5 * 1024 * 1024, // 5MB
    'max_width' => 2000, // 2K max width
    'max_height' => 2000, // 2K max height
    'allowed_exts' => ['jpg', 'jpeg', 'png', 'webp'],
    'allowed_mimes' => [
        'image/jpeg',
        'image/png',
        'image/webp',
    ],
    'output_format' => 'webp', // Convert to WebP for efficiency
    'quality' => 82,
    'uploads_raw' => $UPLOADS_RAW,
    'uploads' => $UPLOADS,
];