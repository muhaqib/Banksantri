<?php

/**
 * Generate PWA icons from logo.png
 * Run this script: php generate-icons.php
 */

$logoPath = __DIR__ . '/public/images/logo.png';
$iconsDir = __DIR__ . '/public/images/icons';

if (!file_exists($logoPath)) {
    echo "Logo file not found at: $logoPath\n";
    exit(1);
}

// Create icons directory if it doesn't exist
if (!is_dir($iconsDir)) {
    mkdir($iconsDir, 0755, true);
}

$sizes = [72, 96, 128, 144, 152, 192, 384, 512];

// Check if GD extension is available
if (!extension_loaded('gd')) {
    echo "GD extension is required. Please install it.\n";
    echo "On macOS with Homebrew: brew install php@8.3\n";
    echo "Or use the manual method described below.\n";
    exit(1);
}

$logo = imagecreatefrompng($logoPath);
$logoWidth = imagesx($logo);
$logoHeight = imagesy($logo);

foreach ($sizes as $size) {
    $newImage = imagecreatetruecolor($size, $size);
    
    // Preserve transparency
    imagealphablending($newImage, false);
    imagesavealpha($newImage, true);
    
    // Copy and resize
    imagecopyresampled(
        $newImage, $logo,
        0, 0, 0, 0,
        $size, $size,
        $logoWidth, $logoHeight
    );
    
    $outputPath = "$iconsDir/icon-{$size}x{$size}.png";
    imagepng($newImage, $outputPath);
    imagedestroy($newImage);
    
    echo "Created: $outputPath\n";
}

imagedestroy($logo);

echo "\n✅ All icons generated successfully!\n";
