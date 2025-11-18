<?php
/**
 * Generate test fixture images for testing
 *
 * This script creates sample images in various formats for testing purposes.
 */

$fixturesDir = __DIR__ . '/images/';

if (!is_dir($fixturesDir)) {
    mkdir($fixturesDir, 0755, true);
}

echo "Generating test fixtures...\n";

// 1. Create a simple JPEG image (100x100)
$img = imagecreatetruecolor(100, 100);
$red = imagecolorallocate($img, 255, 0, 0);
imagefilledrectangle($img, 0, 0, 100, 100, $red);
imagejpeg($img, $fixturesDir . 'test-small.jpg', 100);
imagedestroy($img);
echo "✓ Created test-small.jpg (100x100)\n";

// 2. Create a larger JPEG image (800x600)
$img = imagecreatetruecolor(800, 600);
$blue = imagecolorallocate($img, 0, 0, 255);
$white = imagecolorallocate($img, 255, 255, 255);
imagefilledrectangle($img, 0, 0, 800, 600, $blue);
imagefilledrectangle($img, 100, 100, 700, 500, $white);
imagejpeg($img, $fixturesDir . 'test-large.jpg', 100);
imagedestroy($img);
echo "✓ Created test-large.jpg (800x600)\n";

// 3. Create a PNG image with transparency (200x200)
$img = imagecreatetruecolor(200, 200);
imagealphablending($img, false);
imagesavealpha($img, true);
$transparent = imagecolorallocatealpha($img, 0, 0, 0, 127);
imagefilledrectangle($img, 0, 0, 200, 200, $transparent);
$green = imagecolorallocatealpha($img, 0, 255, 0, 0);
imagefilledellipse($img, 100, 100, 150, 150, $green);
imagepng($img, $fixturesDir . 'test-transparent.png', 9);
imagedestroy($img);
echo "✓ Created test-transparent.png (200x200 with transparency)\n";

// 4. Create a solid PNG image (300x300)
$img = imagecreatetruecolor(300, 300);
$yellow = imagecolorallocate($img, 255, 255, 0);
imagefilledrectangle($img, 0, 0, 300, 300, $yellow);
imagepng($img, $fixturesDir . 'test-solid.png', 9);
imagedestroy($img);
echo "✓ Created test-solid.png (300x300)\n";

// 5. Create a GIF image (150x150)
$img = imagecreatetruecolor(150, 150);
$purple = imagecolorallocate($img, 128, 0, 128);
imagefilledrectangle($img, 0, 0, 150, 150, $purple);
imagegif($img, $fixturesDir . 'test.gif');
imagedestroy($img);
echo "✓ Created test.gif (150x150)\n";

// 6. Create a very small JPEG (already under target size)
$img = imagecreatetruecolor(50, 50);
$orange = imagecolorallocate($img, 255, 165, 0);
imagefilledrectangle($img, 0, 0, 50, 50, $orange);
imagejpeg($img, $fixturesDir . 'test-tiny.jpg', 50);
imagedestroy($img);
echo "✓ Created test-tiny.jpg (50x50, low quality)\n";

// 7. Create a large image for compression testing (1920x1080)
$img = imagecreatetruecolor(1920, 1080);
$darkBlue = imagecolorallocate($img, 0, 0, 128);
imagefilledrectangle($img, 0, 0, 1920, 1080, $darkBlue);
// Add some patterns to make it more realistic
for ($i = 0; $i < 100; $i++) {
    $x = rand(0, 1920);
    $y = rand(0, 1080);
    $color = imagecolorallocate($img, rand(0, 255), rand(0, 255), rand(0, 255));
    imagefilledellipse($img, $x, $y, rand(10, 100), rand(10, 100), $color);
}
imagejpeg($img, $fixturesDir . 'test-hd.jpg', 90);
imagedestroy($img);
echo "✓ Created test-hd.jpg (1920x1080 for compression testing)\n";

echo "\n✅ All fixtures generated successfully!\n";
echo "Location: " . realpath($fixturesDir) . "\n";
