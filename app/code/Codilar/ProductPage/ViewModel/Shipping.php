<?php

declare(strict_types=1);

namespace Codilar\ProductPage\ViewModel;

use Codilar\ProductPage\Model\Config;
use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * Store-wide "Shipping and Returns" text for the details accordion.
 */
class Shipping implements ArgumentInterface
{
    public function __construct(
        private Config $config
    ) {
    }

    public function getText(): string
    {
        return $this->config->getValue('shipping/text');
    }

    public function getLearnMoreUrl(): string
    {
        return $this->config->getUrl('shipping/learn_more_url');
    }
}
