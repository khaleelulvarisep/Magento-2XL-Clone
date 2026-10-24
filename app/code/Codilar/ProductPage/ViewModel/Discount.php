<?php

declare(strict_types=1);

namespace Codilar\ProductPage\ViewModel;

use Magento\Catalog\Model\Product;
use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * Works out the "-20% OFF" label from regular vs final price.
 */
class Discount implements ArgumentInterface
{
    public function getPercent(Product $product): int
    {
        $priceInfo = $product->getPriceInfo();
        $regular = (float) $priceInfo->getPrice('regular_price')->getAmount()->getValue();
        $final = (float) $priceInfo->getPrice('final_price')->getAmount()->getValue();

        if ($regular <= 0 || $final >= $regular) {
            return 0;
        }

        return (int) round(($regular - $final) / $regular * 100);
    }
}
