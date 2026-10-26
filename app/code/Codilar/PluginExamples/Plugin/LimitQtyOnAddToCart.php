<?php

declare(strict_types=1);

namespace Codilar\PluginExamples\Plugin;

use Codilar\PluginExamples\Model\CartQtyLimit;
use Magento\Catalog\Model\Product;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote;

/**
 * BEFORE plugin: runs before Quote::addProduct() and throws if the add would exceed the per-product limit.
 * Every storefront "add to cart" (product page, listing, wishlist, reorder) goes through addProduct().
 */
class LimitQtyOnAddToCart
{
    public function __construct(
        private CartQtyLimit $cartQtyLimit
    ) {
    }

    /**
     * Returning null leaves the original arguments unchanged; throwing stops the original method from running.
     *
     * @param DataObject|float|int|null $request
     * @throws LocalizedException
     */
    public function beforeAddProduct(Quote $subject, Product $product, $request = null): ?array
    {
        $requestedQty = match (true) {
            $request instanceof DataObject => (float) ($request->getQty() ?: 1),
            is_numeric($request) => (float) $request,
            default => 1.0,
        };

        $this->cartQtyLimit->assertAllowed(
            $this->cartQtyLimit->getQtyInCart($subject, (int) $product->getId()) + $requestedQty,
            (string) $product->getName()
        );

        return null;
    }
}
