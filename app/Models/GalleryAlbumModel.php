<?php

namespace App\Models;

use CodeIgniter\Model;

class GalleryAlbumModel extends Model
{
    protected $table         = 'gallery_albums';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'name',
        'slug',
        'eyebrow',
        'location',
        'summary',
        'intro_text',
        'cover_image_url',
        'hero_image_url',
        'event_date',
        'is_active',
        'sort_order',
    ];

    public function active(): static
    {
        return $this->where('is_active', 1)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('event_date', 'DESC')
            ->orderBy('name', 'ASC');
    }

    public function uniqueSlug(string $baseSlug, ?int $ignoreId = null): string
    {
        $slug   = $baseSlug;
        $suffix = 1;

        while (true) {
            $existing = $this->where('slug', $slug)->first();

            if (! $existing || ($ignoreId !== null && (int) $existing['id'] === $ignoreId)) {
                return $slug;
            }

            $slug = $baseSlug . '-' . $suffix;
            $suffix++;
        }
    }
}
