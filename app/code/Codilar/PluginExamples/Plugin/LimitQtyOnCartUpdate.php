<?php

declare(strict_types=1);

namespace Codilar\PluginExamples\Plugin;

use Codilar\PluginExamples\Model\CartQtyLimit;
use Magento\Checkout\Model\Cart;
use Magento\Framework\Exception\LocalizedException;

/**
 * BEFORE plugin: covers changing the qty of a line already in the cart
 * (cart page "Update Shopping Cart" and the minicart qty box both call Cart::updateItems()).
 */
class LimitQtyOnCartUpdate
{
    public function __construct(
        private CartQtyLimit $cartQtyLimit
    ) {
    }

    /**
     * @param array $data [quote item id => ['qty' => float, 'remove' => bool]]
     * @throws LocalizedException
     */
    public function beforeUpdateItems(Cart $subject, $data): ?array
    {
        $quote = $subject->getQuote();

        // New qty per product, so two lines of the same product are checked together
        $newQtyByProduct = [];
        $updatedItemIds = [];
        $names = [];
        foreach ((array) $data as $itemId => $itemInfo) {
            $item = $quote->getItemById($itemId);
            if (!$item || !empty($itemInfo['remove']) || !isset($itemInfo['qty'])) {
                continue;
            }
            $productId = (int) $item->getProductId();
            $newQtyByProduct[$productId] = ($newQtyByProduct[$productId] ?? 0) + (float) $itemInfo['qty'];
            $updatedItemIds[$productId][] = (int) $itemId;
            $names[$productId] = (string) $item->getName();
        }

        foreach ($newQtyByProduct as $productId => $newQty) {
            $untouchedQty = $this->cartQtyLimit->getQtyInCart($quote, $productId, $updatedItemIds[$productId]);
            $this->cartQtyLimit->assertAllowed($untouchedQty + $newQty, $names[$productId]);
        }

        return null;
    }
}
