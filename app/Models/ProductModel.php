<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductModel extends Model
{
    protected $table         = 'products';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'name',
        'slug',
        'sku',
        'category',
        'category_id',
        'short_description',
        'description',
        'image_url',
        'price_label',
        'is_active',
        'sort_order',
    ];

    /** Active products ordered for storefront display. */
    public function active(): static
    {
        return $this->where('is_active', 1)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('name', 'ASC');
    }

    /**
     * Distinct category strings currently in use.
     * Still fast — indexed VARCHAR, no JOIN needed for storefront display.
     */
    public function categories(): array
    {
        $rows = $this->select('category')->distinct()->orderBy('category', 'ASC')->findAll();

        return array_values(
            array_filter(
                array_map(static fn(array $row): string => (string) $row['category'], $rows)
            )
        );
    }

    /**
     * Check whether a SKU already exists, optionally ignoring one product id.
     * Used by the admin form and SKU suggest endpoint.
     */
    public function skuExists(string $sku, ?int $ignoreId = null): bool
    {
        if ($sku === '') {
            return false;
        }

        $builder = $this->where('sku', $sku);
        if ($ignoreId !== null) {
            $builder->where('id !=', $ignoreId);
        }

        return $builder->countAllResults() > 0;
    }

    /**
     * Suggest the next available SKU for a given category prefix.
     * Pattern: BSAS-{CAT3}-{NNNN} where CAT3 is the first 3 alpha chars
     * of the category name uppercased, and NNNN is zero-padded sequence.
     */
    public function nextSkuForPrefix(string $categoryName): string
    {
        $alpha  = preg_replace('/[^A-Za-z]/', '', $categoryName);
        $code   = strtoupper(substr($alpha, 0, 3));
        $code   = str_pad($code, 3, 'X');
        $prefix = 'BSAS-' . $code . '-';

        $existing = $this->like('sku', $prefix, 'after')
            ->orderBy('sku', 'DESC')
            ->findAll();

        $maxSeq = 0;
        foreach ($existing as $product) {
            $suffix = str_replace($prefix, '', (string) $product['sku']);
            if (ctype_digit($suffix) && (int) $suffix > $maxSeq) {
                $maxSeq = (int) $suffix;
            }
        }

        return $prefix . str_pad((string) ($maxSeq + 1), 4, '0', STR_PAD_LEFT);
    }
}
