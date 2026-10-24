<?php

declare(strict_types=1);

namespace Codilar\ProductPage\ViewModel;

use Magento\Catalog\Helper\Output;
use Magento\Catalog\Model\Product;
use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * HTML for the "Product Description" accordion item: description text and care instructions.
 */
class ProductDetails implements ArgumentInterface
{
    public function __construct(
        private Output $outputHelper
    ) {
    }

    public function getDescriptionHtml(Product $product): string
    {
        return $this->getAttributeHtml($product, 'description');
    }

    public function getCareHtml(Product $product): string
    {
        return $this->getAttributeHtml($product, 'care_instructions');
    }

    /**
     * Storefront-safe HTML for one specification value (same filtering as Magento's attributes.phtml).
     */
    public function getSpecValueHtml(Product $product, string $value, string $code): string
    {
        return (string) $this->outputHelper->productAttribute($product, $value, $code);
    }

    private function getAttributeHtml(Product $product, string $code): string
    {
        $value = (string) $product->getData($code);
        if (trim(strip_tags($value)) === '') {
            return '';
        }

        return (string) $this->outputHelper->productAttribute($product, $value, $code);
    }
}
