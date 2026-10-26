<?php

declare(strict_types=1);

namespace Codilar\PluginExamples\Plugin;

use Magento\Checkout\CustomerData\Cart;
use Magento\Checkout\Helper\Data as CheckoutHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * AFTER plugin: takes the minicart's customer-data section and adds a "free shipping" progress message to it.
 * The original method still builds everything; we only append keys to its return value.
 */
class AddFreeShippingProgressToMinicart
{
    private const XML_PATH_FREE_SHIPPING_SUBTOTAL = 'carriers/freeshipping/free_shipping_subtotal';

    public function __construct(
        private ScopeConfigInterface $scopeConfig,
        private CheckoutHelper $checkoutHelper,
        private float $defaultThreshold = 999.0
    ) {
    }

    /**
     * An after plugin receives the original return value and must return the (possibly changed) value.
     */
    public function afterGetSectionData(Cart $subject, array $result): array
    {
        $result['free_shipping_message'] = '';
        $result['free_shipping_percent'] = 0;

        if (empty($result['summary_count'])) {
            return $result;
        }

        $threshold = $this->getThreshold();
        $subtotal = (float) ($result['subtotalAmount'] ?? 0);
        $remaining = $threshold - $subtotal;

        $result['free_shipping_percent'] = (int) min(100, round($subtotal / $threshold * 100));
        $result['free_shipping_message'] = $remaining > 0
            ? (string) __('Add %1 more to get FREE shipping', strip_tags($this->checkoutHelper->formatPrice($remaining)))
            : (string) __('You have unlocked FREE shipping!');

        return $result;
    }

    /**
     * Uses Stores > Configuration > Sales > Delivery Methods > Free Shipping > Minimum Order Amount when set.
     */
    private function getThreshold(): float
    {
        $configured = (float) $this->scopeConfig->getValue(
            self::XML_PATH_FREE_SHIPPING_SUBTOTAL,
            ScopeInterface::SCOPE_STORE
        );

        return $configured > 0 ? $configured : $this->defaultThreshold;
    }
}
