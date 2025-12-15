<?php

namespace App\Models;

use Spatie\MediaLibrary\MediaCollections\Models\Media as BaseMedia;

class Media extends BaseMedia
{
    /**
     * Register media conversions for all uploaded images.
     * This automatically converts all images to WebP format.
     */
    public function registerMediaConversions(\Spatie\MediaLibrary\Conversions\Conversion $conversion = null): void
    {
        $this->addMediaConversion('webp')
            ->format('webp')
            ->quality(85)
            ->performOnCollections('*');

        // Optional: Create a thumbnail version in WebP
        $this->addMediaConversion('thumb')
            ->format('webp')
            ->width(300)
            ->height(300)
            ->quality(85)
            ->performOnCollections('*');
    }
}
