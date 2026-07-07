<?php

namespace App\Models;

use CodeIgniter\Model;

class OemModel extends Model
{
    protected $table         = 'oems';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'name',
        'slug',
        'description',
        'is_active',
        'sort_order',
    ];

    /** All active OEMs ordered for dropdowns. */
    public function active(): static
    {
        return $this->where('is_active', 1)->orderBy('sort_order', 'ASC')->orderBy('name', 'ASC');
    }

    /** Find an OEM by exact name. */
    public function findByName(string $name): ?array
    {
        $result = $this->where('name', trim($name))->first();

        return $result ?: null;
    }

    /** Generate a unique slug, appending a suffix if needed. */
    public function uniqueSlug(string $base, ?int $ignoreId = null): string
    {
        $slug   = $base;
        $suffix = 1;

        while (true) {
            $existing = $this->where('slug', $slug)->first();
            if (! $existing || ($ignoreId !== null && (int) $existing['id'] === $ignoreId)) {
                return $slug;
            }

            $slug = $base . '-' . $suffix;
            $suffix++;
        }
    }

    /**
     * Return the number of vehicles assigned to each OEM.
     * Returns ['id' => count, ...] map.
     */
    public function vehicleCounts(): array
    {
        $db   = \Config\Database::connect();
        $rows = $db->query(
            'SELECT oem_id, COUNT(*) AS cnt FROM vehicles WHERE oem_id IS NOT NULL GROUP BY oem_id'
        )->getResultArray();

        $map = [];
        foreach ($rows as $row) {
            $map[(int) $row['oem_id']] = (int) $row['cnt'];
        }

        return $map;
    }
}
