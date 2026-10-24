<?php

declare(strict_types=1);

namespace Codilar\ProductPage\ViewModel;

use Codilar\ProductPage\Model\Config;
use Magento\Catalog\Model\Product;
use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * "Earn X points" = final price x configured rate.
 */
class LoyaltyPoints implements ArgumentInterface
{
    public function __construct(
        private Config $config
    ) {
    }

    public function isEnabled(): bool
    {
        return $this->config->isEnabled('points');
    }

    public function getPoints(Product $product): int
    {
        $finalPrice = (float) $product->getPriceInfo()->getPrice('final_price')->getAmount()->getValue();

        return (int) floor($finalPrice * (float) $this->config->getValue('points/rate'));
    }
}
