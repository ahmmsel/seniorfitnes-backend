# WebP Image Conversion Configuration

## Overview

All images uploaded through Filament admin panel are automatically converted to WebP format for optimal performance and storage efficiency.

## Implementation

### 1. Custom Media Model (`app/Models/Media.php`)

-   Extends Spatie Media Library's base Media model
-   Automatically registers WebP conversions for all uploaded images
-   Creates two versions:
    -   **webp**: Full-size WebP (85% quality)
    -   **thumb**: 300x300 WebP thumbnail (85% quality)

### 2. Global Filament Configuration (`app/Providers/AppServiceProvider.php`)

-   Configures all `SpatieMediaLibraryFileUpload` components globally
-   Sets default conversion to 'webp'
-   Accepts: JPEG, PNG, GIF, WebP
-   Max file size: 10MB

### 3. Media Library Config (`config/media-library.php`)

-   Uses custom `App\Models\Media` model
-   Image driver: `imagick` (better WebP support than GD)
-   Includes Cwebp optimizer for additional compression

## Usage

### In Filament Resources

No changes needed! All existing `SpatieMediaLibraryFileUpload` fields automatically use WebP:

```php
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;

SpatieMediaLibraryFileUpload::make('image')
    ->label('Image')
    ->collection('default')
    ->required();
```

### Retrieving Images

```php
// Get WebP conversion URL
$model->getFirstMediaUrl('default', 'webp');

// Get thumbnail URL
$model->getFirstMediaUrl('default', 'thumb');

// Get original (still available)
$model->getFirstMediaUrl('default');
```

### In API Responses

Update your model accessors to serve WebP by default:

```php
public function getImageUrlAttribute(): ?string
{
    return $this->getFirstMediaUrl('default', 'webp') ?: null;
}

public function getThumbUrlAttribute(): ?string
{
    return $this->getFirstMediaUrl('default', 'thumb') ?: null;
}
```

## Benefits

-   **Smaller file sizes**: WebP typically 25-35% smaller than JPEG/PNG
-   **Better quality**: Same visual quality at smaller sizes
-   **Faster loading**: Reduced bandwidth and faster API responses
-   **Automatic**: Zero code changes in existing Filament resources

## Requirements

-   PHP Imagick extension (already installed)
-   Sufficient storage for original + conversions
-   Queue worker for async conversion (optional but recommended)

## Environment Variables

```env
IMAGE_DRIVER=imagick
QUEUE_CONVERSIONS_BY_DEFAULT=true
```

## Troubleshooting

### If conversions don't generate:

```bash
# Clear media cache
php artisan cache:clear

# Regenerate conversions for existing media
php artisan media-library:regenerate
```

### If using queues:

```bash
# Make sure queue worker is running
php artisan queue:work

# Or use Supervisor (production)
```

## Browser Support

WebP is supported in all modern browsers (Chrome, Firefox, Safari 14+, Edge).
