# imageResize Examples

This directory contains practical examples demonstrating the various features of the imageResize library.

## Prerequisites

1. Install dependencies:
   ```bash
   composer install
   ```

2. Generate test fixtures (if not already done):
   ```bash
   php tests/Fixtures/generate_fixtures.php
   ```

3. Create output directory:
   ```bash
   mkdir -p examples/output
   ```

## Running Examples

Each example file can be run independently:

```bash
php examples/01_crop_example.php
php examples/02_filter_example.php
php examples/03_watermark_example.php
php examples/04_combined_example.php
```

## Example Files

### 01_crop_example.php
Demonstrates image cropping functionality:
- **Center crop**: Automatically crop from center
- **Custom position crop**: Crop from specific coordinates
- **Resize and crop**: Resize to fill dimensions, then crop
- **PNG transparency**: Preserve transparency during crop

**Use cases**: Creating thumbnails, profile pictures, social media images

### 02_filter_example.php
Demonstrates image filter application:
- **Basic filters**: Grayscale, sepia, negate
- **Blur/Sharpen**: Control image sharpness
- **Brightness/Contrast**: Adjust image exposure
- **Artistic effects**: Emboss, edge detection, pixelate
- **Color effects**: Colorize with custom tints
- **Filter chains**: Apply multiple filters in sequence

**Use cases**: Photo enhancement, artistic effects, image preprocessing

### 03_watermark_example.php
Demonstrates watermarking capabilities:
- **Text watermarks**: Add copyright, branding text
- **Image watermarks**: Add logo overlays
- **Positioning**: 5 preset positions + custom coordinates
- **Opacity control**: From fully transparent to fully opaque
- **Scaling**: Resize watermarks proportionally
- **Font support**: Built-in fonts + TrueType fonts

**Use cases**: Copyright protection, branding, photo attribution

### 04_combined_example.php
Demonstrates complete image processing workflows:
- **Social media workflow**: Resize → Crop → Filter → Watermark
- **E-commerce workflow**: Product photo optimization
- **Thumbnail generation**: Multiple sizes from single source
- **Artistic effects**: Complex filter combinations

**Use cases**: Complete image processing pipelines, batch workflows

## Output

All examples save processed images to `examples/output/`. You can compare the original images from `tests/Fixtures/images/` with the processed results.

## Common Patterns

### Pattern 1: Create Square Thumbnails
```php
imageResize::crop(
    'source.jpg',
    'thumb.jpg',
    500,
    500,
    null,
    null,
    true  // Resize to fill before cropping
);
```

### Pattern 2: Enhance Photo Quality
```php
imageResize::filterChain(
    'photo.jpg',
    'enhanced.jpg',
    [
        ['type' => 'brightness', 'options' => ['level' => 20]],
        ['type' => 'contrast', 'options' => ['level' => -15]],
        ['type' => 'sharpen']
    ]
);
```

### Pattern 3: Add Branding
```php
imageResize::watermarkImage(
    'product.jpg',
    'branded.jpg',
    'logo.png',
    [
        'position' => 'bottom-right',
        'opacity' => 70,
        'scale' => 50
    ]
);
```

## Customization

All examples use default options. You can customize:
- **Quality**: Set `imageResize::$quality` (0-100)
- **Target size**: Set `imageResize::$targetSize` (bytes)
- **Position**: Use 'center', 'top-left', 'top-right', 'bottom-left', 'bottom-right'
- **Colors**: RGB arrays like `[255, 0, 0]` for red
- **Opacity**: 0-127 for text, 0-100 for images

## Troubleshooting

### "Fixture images not found"
Run `php tests/Fixtures/generate_fixtures.php` first.

### "Output directory not writable"
Ensure `examples/output/` exists and is writable:
```bash
mkdir -p examples/output
chmod 755 examples/output
```

### "WebP not supported"
Your PHP installation needs GD with WebP support. Check:
```bash
php -r "echo function_exists('imagewebp') ? 'Yes' : 'No';"
```

### "TrueType fonts not working"
Ensure you have a `.ttf` font file and pass the path via `font_file` option.

## Next Steps

1. Review the main [README.md](../README.md) for complete API documentation
2. Check [CHANGELOG.md](../CHANGELOG.md) for version history
3. See [tests/](../tests/) for comprehensive test examples
4. Read [CONTRIBUTING.md](../CONTRIBUTING.md) if you want to contribute

## Support

- Issues: https://github.com/MasumNishat/imageResize/issues
- Documentation: https://github.com/MasumNishat/imageResize
