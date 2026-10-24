<?php

declare(strict_types=1);

namespace Codilar\GuestWishlist\Model;

use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;

/**
 * Random token in a cookie that identifies a guest's wishlist (no personal data).
 */
class GuestToken
{
    private const COOKIE_NAME = 'codilar_guest_wishlist';
    private const LIFETIME = 30 * 24 * 60 * 60;

    public function __construct(
        private CookieManagerInterface $cookieManager,
        private CookieMetadataFactory $cookieMetadataFactory,
        private SessionManagerInterface $sessionManager
    ) {
    }

    public function get(): ?string
    {
        $token = $this->cookieManager->getCookie(self::COOKIE_NAME);

        return is_string($token) && preg_match('/^[a-f0-9]{64}$/', $token) ? $token : null;
    }

    public function getOrCreate(): string
    {
        $token = $this->get() ?? bin2hex(random_bytes(32));
        $metadata = $this->cookieMetadataFactory->createPublicCookieMetadata()
            ->setDuration(self::LIFETIME)
            ->setPath($this->sessionManager->getCookiePath())
            ->setDomain($this->sessionManager->getCookieDomain())
            ->setHttpOnly(true)
            ->setSameSite('Lax');
        $this->cookieManager->setPublicCookie(self::COOKIE_NAME, $token, $metadata);

        return $token;
    }

    public function delete(): void
    {
        $this->cookieManager->deleteCookie(
            self::COOKIE_NAME,
            $this->cookieMetadataFactory->createCookieMetadata()
                ->setPath($this->sessionManager->getCookiePath())
                ->setDomain($this->sessionManager->getCookieDomain())
        );
    }
}
