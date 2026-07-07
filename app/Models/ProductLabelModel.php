<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductLabelModel extends Model
{
    protected $table         = 'product_labels';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = ''; // table has no updated_at column
    protected $allowedFields = [
        'product_id',
        'label_id',
    ];

    /** Replace a product's linked labels with the given set (delete then re-insert). */
    public function syncForProduct(int $productId, array $labelIds): void
    {
        $this->where('product_id', $productId)->delete();

        $labelIds = array_values(array_unique(array_filter(array_map('intval', $labelIds), static fn(int $id): bool => $id > 0)));

        foreach ($labelIds as $labelId) {
            $this->insert([
                'product_id' => $productId,
                'label_id'   => $labelId,
            ]);
        }
    }

    /** Label IDs currently linked to a product — used to pre-check the admin form. */
    public function labelIdsForProduct(int $productId): array
    {
        $rows = $this->where('product_id', $productId)->findAll();

        return array_map(static fn(array $row): int => (int) $row['label_id'], $rows);
    }
}
