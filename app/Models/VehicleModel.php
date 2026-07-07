<?php

namespace App\Models;

use CodeIgniter\Model;

class VehicleModel extends Model
{
    protected $table         = 'vehicles';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'name',
        'slug',
        'category_id',
        'oem_id',
        'description',
        'is_active',
        'sort_order',
    ];

    /** All active vehicles ordered for dropdowns/checklists. */
    public function active(): static
    {
        return $this->where('is_active', 1)->orderBy('sort_order', 'ASC')->orderBy('name', 'ASC');
    }

    /** Find a vehicle by exact name. */
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
     * Return the number of products linked to each vehicle via the product_vehicles pivot.
     * Returns ['id' => count, ...] map.
     */
    public function productCounts(): array
    {
        $rows = $this->db->query(
            'SELECT vehicle_id, COUNT(*) AS cnt FROM product_vehicles GROUP BY vehicle_id'
        )->getResultArray();

        $map = [];
        foreach ($rows as $row) {
            $map[(int) $row['vehicle_id']] = (int) $row['cnt'];
        }

        return $map;
    }

    /**
     * Active vehicles for a single OEM, each carrying a `product_count` (via the
     * product_vehicles pivot). Used by the storefront's cascading OEM -> Vehicle
     * filter step once an OEM has been selected.
     */
    public function vehiclesForOem(int $oemId): array
    {
        $rows = $this->db->query("
            SELECT v.id, v.name, v.slug, COUNT(pv.product_id) AS product_count
            FROM vehicles v
            LEFT JOIN product_vehicles pv ON pv.vehicle_id = v.id
            WHERE v.is_active = 1 AND v.oem_id = ?
            GROUP BY v.id, v.name, v.slug
            ORDER BY v.sort_order ASC, v.name ASC
        ", [$oemId])->getResultArray();

        return array_map(
            static fn(array $r): array => [
                'id'            => (int) $r['id'],
                'name'          => (string) $r['name'],
                'slug'          => (string) $r['slug'],
                'product_count' => (int) $r['product_count'],
            ],
            $rows
        );
    }

    /**
     * Active vehicles grouped by their parent OEM name, each vehicle carrying
     * a `product_count` (via the product_vehicles pivot). OEMs with no active
     * vehicles are omitted. Vehicles without an OEM are grouped under "Other".
     * Used by the admin product form's compatibility checklist.
     */
    public function groupedByOem(): array
    {
        $rows = $this->db->query("
            SELECT v.id, v.name, v.slug, v.oem_id, o.name AS oem_name,
                   COUNT(pv.product_id) AS product_count
            FROM vehicles v
            LEFT JOIN oems o ON o.id = v.oem_id
            LEFT JOIN product_vehicles pv ON pv.vehicle_id = v.id
            WHERE v.is_active = 1
            GROUP BY v.id, v.name, v.slug, v.oem_id, o.name
            ORDER BY o.sort_order ASC, o.name ASC, v.sort_order ASC, v.name ASC
        ")->getResultArray();

        $grouped = [];
        foreach ($rows as $row) {
            $oemName = $row['oem_name'] ?? 'Other';
            $grouped[$oemName][] = [
                'id'            => (int) $row['id'],
                'name'          => (string) $row['name'],
                'slug'          => (string) $row['slug'],
                'oem_id'        => $row['oem_id'] !== null ? (int) $row['oem_id'] : null,
                'product_count' => (int) $row['product_count'],
            ];
        }

        return $grouped;
    }
}
