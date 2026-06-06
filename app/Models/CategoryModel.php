<?php

namespace App\Models;

use CodeIgniter\Model;

class CategoryModel extends Model
{
    protected $table         = 'categories';
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

    /** All active categories ordered for dropdowns. */
    public function active(): static
    {
        return $this->where('is_active', 1)->orderBy('sort_order', 'ASC')->orderBy('name', 'ASC');
    }

    /** Find a category by exact name. MySQL VARCHAR comparison is case-insensitive by default. */
    public function findByName(string $name): ?array
    {
        $result = $this->where('name', trim($name))->first();

        return $result ?: null;
    }

    /**
     * Find an existing category or create a new one from a plain name string.
     * Used by bulk-import so CSV rows don't need category IDs.
     */
    public function findOrCreate(string $name): array
    {
        $name = trim($name);
        $existing = $this->findByName($name);

        if ($existing) {
            return $existing;
        }

        $slug = $this->uniqueSlug(url_title($name, '-', true));

        $this->insert([
            'name'       => $name,
            'slug'       => $slug,
            'is_active'  => 1,
            'sort_order' => 0,
        ]);

        return $this->find((int) $this->getInsertID());
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
     * Return the number of products assigned to each category.
     * Returns ['id' => count, ...] map.
     */
    public function productCounts(): array
    {
        $db   = \Config\Database::connect();
        $rows = $db->query(
            'SELECT category_id, COUNT(*) AS cnt FROM products WHERE category_id IS NOT NULL GROUP BY category_id'
        )->getResultArray();

        $map = [];
        foreach ($rows as $row) {
            $map[(int) $row['category_id']] = (int) $row['cnt'];
        }

        return $map;
    }
}
