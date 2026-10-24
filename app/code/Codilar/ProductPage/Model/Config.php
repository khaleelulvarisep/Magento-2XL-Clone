<?php

declare(strict_types=1);

namespace Codilar\ProductPage\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Reads the Stores > Configuration > Codilar > Product Page settings.
 */
class Config
{
    private const XML_PATH_PREFIX = 'codilar_pdp/';

    public function __construct(
        private ScopeConfigInterface $scopeConfig,
        private UrlInterface $urlBuilder
    ) {
    }

    public function getValue(string $path): string
    {
        return (string) $this->scopeConfig->getValue(
            self::XML_PATH_PREFIX . $path,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function isEnabled(string $group): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_PREFIX . $group . '/enabled',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Turns a configured value into a URL; accepts a full URL or a store-relative path.
     */
    public function getUrl(string $path): string
    {
        $value = trim($this->getValue($path));
        if ($value === '' || preg_match('#^https?://#i', $value)) {
            return $value;
        }

        return $this->urlBuilder->getUrl('', ['_direct' => ltrim($value, '/')]);
    }
}
