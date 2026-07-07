<?php

namespace App\Models;

use CodeIgniter\Model;

class DivisionModel extends Model
{
    protected $table         = 'divisions';
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

    /** All active divisions ordered for dropdowns. */
    public function active(): static
    {
        return $this->where('is_active', 1)->orderBy('sort_order', 'ASC')->orderBy('name', 'ASC');
    }

    /** Find a division by exact name. */
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
     * Return the number of categories assigned to each division.
     * Returns ['id' => count, ...] map.
     */
    public function categoryCounts(): array
    {
        $db   = \Config\Database::connect();
        $rows = $db->query(
            'SELECT division_id, COUNT(*) AS cnt FROM categories WHERE division_id IS NOT NULL GROUP BY division_id'
        )->getResultArray();

        $map = [];
        foreach ($rows as $row) {
            $map[(int) $row['division_id']] = (int) $row['cnt'];
        }

        return $map;
    }
}
