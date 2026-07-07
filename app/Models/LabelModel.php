<?php

namespace App\Models;

use CodeIgniter\Model;

class LabelModel extends Model
{
    protected $table         = 'labels';
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

    /** All active labels ordered for dropdowns/checklists. */
    public function active(): static
    {
        return $this->where('is_active', 1)->orderBy('sort_order', 'ASC')->orderBy('name', 'ASC');
    }

    /** Find a label by exact name. */
    public function findByName(string $name): ?array
    {
        $result = $this->where('name', trim($name))->first();

        return $result ?: null;
    }

    /**
     * Find an existing label or create a new one from a plain name string.
     * Used by bulk-import so CSV rows don't need label IDs.
     */
    public function findOrCreate(string $name): array
    {
        $name     = trim($name);
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
     * Return the number of products linked to each label via the product_labels pivot.
     * Returns ['id' => count, ...] map.
     */
    public function productCounts(): array
    {
        $rows = $this->db->query(
            'SELECT label_id, COUNT(*) AS cnt FROM product_labels GROUP BY label_id'
        )->getResultArray();

        $map = [];
        foreach ($rows as $row) {
            $map[(int) $row['label_id']] = (int) $row['cnt'];
        }

        return $map;
    }
}
