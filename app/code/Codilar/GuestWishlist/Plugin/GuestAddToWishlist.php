<?php

declare(strict_types=1);

namespace Codilar\GuestWishlist\Plugin;

use Codilar\GuestWishlist\Model\GuestToken;
use Codilar\GuestWishlist\Model\GuestWishlist;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Wishlist\Controller\Index\Add;

/**
 * Lets guests use "Add to Wish List": the item is kept in codilar_guest_wishlist until they log in.
 * Logged-in customers go through Magento's normal Add controller untouched.
 */
class GuestAddToWishlist
{
    public function __construct(
        private CustomerSession $customerSession,
        private FormKeyValidator $formKeyValidator,
        private ProductRepositoryInterface $productRepository,
        private StoreManagerInterface $storeManager,
        private GuestToken $guestToken,
        private GuestWishlist $guestWishlist,
        private ManagerInterface $messageManager,
        private RedirectFactory $redirectFactory,
        private RedirectInterface $redirect
    ) {
    }

    public function aroundDispatch(Add $subject, callable $proceed, RequestInterface $request)
    {
        if ($this->customerSession->isLoggedIn()) {
            return $proceed($request);
        }

        $result = $this->redirectFactory->create()->setUrl($this->redirect->getRefererUrl());
        if (!$this->formKeyValidator->validate($request)) {
            return $result;
        }

        $productId = (int) $request->getParam('product');
        try {
            $product = $this->productRepository->getById(
                $productId,
                false,
                (int) $this->storeManager->getStore()->getId()
            );
        } catch (NoSuchEntityException $e) {
            $product = null;
        }
        if (!$product || !$product->isVisibleInCatalog()) {
            $this->messageManager->addErrorMessage(__('We can\'t specify a product.'));

            return $result;
        }

        $buyRequest = array_diff_key($request->getParams(), array_flip(['form_key', 'uenc']));
        $this->guestWishlist->add($this->guestToken->getOrCreate(), $productId, $buyRequest);

        $this->messageManager->addSuccessMessage(
            __('%1 has been added to your Wish List. Sign in to see your Wish List.', $product->getName())
        );

        return $result;
    }
}
