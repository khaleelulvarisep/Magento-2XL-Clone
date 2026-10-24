<?php

declare(strict_types=1);

namespace Codilar\GuestWishlist\Controller\Index;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\View\Result\PageFactory;

/**
 * Guest wishlist page (/guestwishlist). Logged-in customers are sent to their normal wishlist.
 */
class Index implements HttpGetActionInterface
{
    public function __construct(
        private PageFactory $pageFactory,
        private RedirectFactory $redirectFactory,
        private CustomerSession $customerSession
    ) {
    }

    public function execute()
    {
        if ($this->customerSession->isLoggedIn()) {
            return $this->redirectFactory->create()->setPath('wishlist');
        }

        $page = $this->pageFactory->create();
        $page->getConfig()->getTitle()->set(__('My Wish List'));

        return $page;
    }
}
