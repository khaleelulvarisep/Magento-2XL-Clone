<?php

declare(strict_types=1);

namespace Codilar\GuestWishlist\Model;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Reads and writes guest wishlist rows in the codilar_guest_wishlist table.
 */
class GuestWishlist
{
    private const TABLE = 'codilar_guest_wishlist';

    public function __construct(
        private ResourceConnection $resource,
        private Json $json
    ) {
    }

    /**
     * Adds the product, or replaces its options if it is already in the guest's list.
     */
    public function add(string $token, int $productId, array $buyRequest): void
    {
        $this->resource->getConnection()->insertOnDuplicate(
            $this->resource->getTableName(self::TABLE),
            [
                'guest_token' => $token,
                'product_id' => $productId,
                'buy_request' => $this->json->serialize($buyRequest),
            ],
            ['buy_request']
        );
    }

    /**
     * @return array<int, array{product_id: int, buy_request: array}>
     */
    public function getItems(string $token): array
    {
        $connection = $this->resource->getConnection();
        $rows = $connection->fetchAll(
            $connection->select()
                ->from($this->resource->getTableName(self::TABLE), ['product_id', 'buy_request'])
                ->where('guest_token = ?', $token)
                ->order('item_id ASC')
        );

        return array_map(fn (array $row): array => [
            'product_id' => (int) $row['product_id'],
            'buy_request' => $row['buy_request'] ? (array) $this->json->unserialize($row['buy_request']) : [],
        ], $rows);
    }

    public function remove(string $token, int $productId): void
    {
        $this->resource->getConnection()->delete(
            $this->resource->getTableName(self::TABLE),
            ['guest_token = ?' => $token, 'product_id = ?' => $productId]
        );
    }

    public function clear(string $token): void
    {
        $this->resource->getConnection()->delete(
            $this->resource->getTableName(self::TABLE),
            ['guest_token = ?' => $token]
        );
    }
}
