<?php

declare(strict_types=1);

namespace Codilar\ProductPage\ViewModel;

use Codilar\ProductPage\Model\Config;
use Magento\Catalog\Model\Product;
use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * Shows the warehouse box when the product's "Warehouse Pickup" attribute is Yes.
 */
class Warehouse implements ArgumentInterface
{
    public function __construct(
        private Config $config
    ) {
    }

    public function isAvailable(Product $product): bool
    {
        return $this->config->isEnabled('warehouse') && (bool) $product->getData('warehouse_pickup');
    }

    public function getLearnMoreUrl(): string
    {
        return $this->config->getUrl('warehouse/learn_more_url');
    }
}
