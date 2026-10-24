<?php

declare(strict_types=1);

namespace Codilar\ProductPage\ViewModel;

use Codilar\ProductPage\Model\Config;
use Magento\Catalog\Model\Product;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * Tabby / Tamara style "pay later" boxes and their "Learn more" / "More options" popups.
 * Display only - no payment integration. The plans below are example rates (as shown on 2XL),
 * not real quotes from the providers.
 */
class Installments implements ArgumentInterface
{
    /** Number of payments => fee as % of the price */
    private const TABBY_PLANS = [4 => 0, 6 => 5, 8 => 9, 12 => 17];
    private const TAMARA_PLANS = [2 => 0, 3 => 0, 4 => 0, 6 => 0, 9 => 17, 12 => 17];

    public function __construct(
        private Config $config,
        private PriceCurrencyInterface $priceCurrency
    ) {
    }

    public function isEnabled(string $provider): bool
    {
        return $this->config->isEnabled('installments')
            && $this->config->getValue('installments/' . $provider) === '1';
    }

    /**
     * @return array<int, array{count: int, monthly: string, fee: ?string}>
     */
    public function getTabbyPlans(Product $product): array
    {
        return $this->buildPlans($product, self::TABBY_PLANS);
    }

    /**
     * @return array<int, array{count: int, monthly: string, fee: ?string}>
     */
    public function getTamaraPlans(Product $product): array
    {
        return $this->buildPlans($product, self::TAMARA_PLANS);
    }

    /**
     * Number of payments in the cheapest plan with no fee (the "or 4 interest-free payments" text).
     */
    public function getTabbyInterestFreeCount(): int
    {
        return (int) array_search(0, self::TABBY_PLANS, true);
    }

    public function getFormattedPrice(Product $product): string
    {
        return $this->format($this->getFinalPrice($product));
    }

    /**
     * Longest plan, i.e. the lowest monthly amount ("As low as ... /month").
     *
     * @param array<int, array{count: int, monthly: string, fee: ?string}> $plans
     * @return array{count: int, monthly: string, fee: ?string}
     */
    public function getLowestMonthlyPlan(array $plans): array
    {
        return end($plans);
    }

    /**
     * @param array<int, int> $plans
     * @return array<int, array{count: int, monthly: string, fee: ?string}>
     */
    private function buildPlans(Product $product, array $plans): array
    {
        $price = $this->getFinalPrice($product);
        if ($price <= 0) {
            return [];
        }

        $result = [];
        foreach ($plans as $count => $feePercent) {
            $monthlyFee = $price * $feePercent / 100 / $count;
            $result[] = [
                'count' => $count,
                'monthly' => $this->format($price / $count + $monthlyFee),
                'fee' => $feePercent ? $this->format($monthlyFee) : null,
            ];
        }

        return $result;
    }

    private function getFinalPrice(Product $product): float
    {
        return (float) $product->getPriceInfo()->getPrice('final_price')->getAmount()->getValue();
    }

    private function format(float $amount): string
    {
        return $this->priceCurrency->convertAndFormat($amount, false);
    }
}
