<?php

declare(strict_types=1);

namespace Codilar\PluginExamples\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote;

/**
 * Shared rule for the qty-limit plugins: a customer may hold at most $maxQty units of one product in the cart.
 * Configurable/bundle lines are counted by their parent product, so two sizes of the same shirt add up.
 */
class CartQtyLimit
{
    public function __construct(
        private int $maxQty = 2
    ) {
    }

    /**
     * Units of $productId already in the cart, optionally ignoring some quote item ids.
     *
     * @param int[] $excludeItemIds
     */
    public function getQtyInCart(Quote $quote, int $productId, array $excludeItemIds = []): float
    {
        $qty = 0.0;
        foreach ($quote->getAllVisibleItems() as $item) {
            if ((int) $item->getProductId() === $productId && !in_array((int) $item->getId(), $excludeItemIds, true)) {
                $qty += (float) $item->getQty();
            }
        }

        return $qty;
    }

    /**
     * @throws LocalizedException
     */
    public function assertAllowed(float $totalQty, string $productName): void
    {
        if ($totalQty > $this->maxQty) {
            throw new LocalizedException(
                __('You can buy a maximum of %1 units of "%2".', $this->maxQty, $productName)
            );
        }
    }
}
