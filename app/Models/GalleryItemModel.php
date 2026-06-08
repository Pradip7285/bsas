<?php

namespace App\Models;

use CodeIgniter\Model;

class GalleryItemModel extends Model
{
    protected $table         = 'gallery_items';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'album_id',
        'title',
        'caption',
        'image_url',
        'badge_label',
        'display_style',
        'is_featured',
        'is_active',
        'sort_order',
    ];

    public function active(): static
    {
        return $this->where('is_active', 1)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'ASC');
    }

    public function forAlbum(int $albumId): static
    {
        return $this->where('album_id', $albumId);
    }
}
