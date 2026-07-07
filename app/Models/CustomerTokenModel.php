<?php

namespace App\Models;

use CodeIgniter\Model;

class CustomerTokenModel extends Model
{
    protected $table         = 'customer_tokens';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'customer_id',
        'purpose',
        'token_hash',
        'expires_at',
        'used_at',
        'created_at',
    ];

    /**
     * Create a token for the given customer/purpose, returning the raw (unhashed)
     * token to email to the customer. Only the SHA-256 hash is persisted.
     */
    public function issue(int $customerId, string $purpose, int $ttlSeconds): string
    {
        $raw = bin2hex(random_bytes(32));

        $this->insert([
            'customer_id' => $customerId,
            'purpose'     => $purpose,
            'token_hash'  => hash('sha256', $raw),
            'expires_at'  => date('Y-m-d H:i:s', time() + $ttlSeconds),
            'created_at'  => date('Y-m-d H:i:s'),
        ]);

        return $raw;
    }

    /** Validate and consume a raw token, returning its row or null if invalid/expired/used. */
    public function consume(string $rawToken, string $purpose): ?array
    {
        $row = $this->where('token_hash', hash('sha256', $rawToken))
            ->where('purpose', $purpose)
            ->first();

        if (! $row || $row['used_at'] !== null || strtotime((string) $row['expires_at']) < time()) {
            return null;
        }

        $this->update($row['id'], ['used_at' => date('Y-m-d H:i:s')]);

        return $row;
    }
}
