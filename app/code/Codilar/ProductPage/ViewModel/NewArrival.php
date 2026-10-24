<?php

declare(strict_types=1);

namespace Codilar\ProductPage\ViewModel;

use Magento\Catalog\Model\Product;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * Uses the product's "Set Product as New From / To" dates to decide on the "New Arrival" badge.
 */
class NewArrival implements ArgumentInterface
{
    public function __construct(
        private TimezoneInterface $timezone
    ) {
    }

    public function isNew(Product $product): bool
    {
        $from = $product->getData('news_from_date');
        $to = $product->getData('news_to_date');

        if (!$from) {
            return false;
        }

        return $this->timezone->isScopeDateInInterval($product->getStore(), $from, $to);
    }
}
