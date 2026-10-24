<?php

declare(strict_types=1);

namespace Codilar\GuestWishlist\Plugin;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Wishlist\Controller\Index\Index;

/**
 * /wishlist for guests: show the guest wishlist page instead of sending them to the login page.
 */
class RedirectGuestToGuestWishlist
{
    public function __construct(
        private CustomerSession $customerSession,
        private RedirectFactory $redirectFactory
    ) {
    }

    public function aroundDispatch(Index $subject, callable $proceed, RequestInterface $request)
    {
        if ($this->customerSession->isLoggedIn()) {
            return $proceed($request);
        }

        return $this->redirectFactory->create()->setPath('guestwishlist');
    }
}
