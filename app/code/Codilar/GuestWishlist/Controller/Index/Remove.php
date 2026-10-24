<?php

declare(strict_types=1);

namespace Codilar\GuestWishlist\Controller\Index;

use Codilar\GuestWishlist\Model\GuestToken;
use Codilar\GuestWishlist\Model\GuestWishlist;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\Message\ManagerInterface;

/**
 * Removes one product from the guest wishlist (POST with form key).
 */
class Remove implements HttpPostActionInterface
{
    public function __construct(
        private RequestInterface $request,
        private FormKeyValidator $formKeyValidator,
        private GuestToken $guestToken,
        private GuestWishlist $guestWishlist,
        private ManagerInterface $messageManager,
        private RedirectFactory $redirectFactory
    ) {
    }

    public function execute()
    {
        $token = $this->guestToken->get();
        $productId = (int) $this->request->getParam('product');
        if ($token && $productId && $this->formKeyValidator->validate($this->request)) {
            $this->guestWishlist->remove($token, $productId);
            $this->messageManager->addSuccessMessage(__('The item has been removed from your Wish List.'));
        }

        return $this->redirectFactory->create()->setPath('guestwishlist');
    }
}
