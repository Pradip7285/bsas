<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductVehicleModel extends Model
{
    protected $table         = 'product_vehicles';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = ''; // table has no updated_at column
    protected $allowedFields = [
        'product_id',
        'vehicle_id',
    ];

    /** Replace a product's linked vehicles with the given set (delete then re-insert). */
    public function syncForProduct(int $productId, array $vehicleIds): void
    {
        $this->where('product_id', $productId)->delete();

        $vehicleIds = array_values(array_unique(array_filter(array_map('intval', $vehicleIds), static fn(int $id): bool => $id > 0)));

        foreach ($vehicleIds as $vehicleId) {
            $this->insert([
                'product_id' => $productId,
                'vehicle_id' => $vehicleId,
            ]);
        }
    }

    /** Vehicle IDs currently linked to a product — used to pre-check the admin form. */
    public function vehicleIdsForProduct(int $productId): array
    {
        $rows = $this->where('product_id', $productId)->findAll();

        return array_map(static fn(array $row): int => (int) $row['vehicle_id'], $rows);
    }
}
