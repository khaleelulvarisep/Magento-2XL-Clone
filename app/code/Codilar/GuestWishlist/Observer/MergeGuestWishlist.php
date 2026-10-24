<?php

declare(strict_types=1);

namespace Codilar\GuestWishlist\Observer;

use Codilar\GuestWishlist\Model\GuestToken;
use Codilar\GuestWishlist\Model\GuestWishlist;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Wishlist\Model\WishlistFactory;
use Psr\Log\LoggerInterface;

/**
 * On login, moves the guest's wishlist items into the customer's wishlist and forgets the guest list.
 */
class MergeGuestWishlist implements ObserverInterface
{
    public function __construct(
        private GuestToken $guestToken,
        private GuestWishlist $guestWishlist,
        private WishlistFactory $wishlistFactory,
        private ProductRepositoryInterface $productRepository,
        private StoreManagerInterface $storeManager,
        private LoggerInterface $logger
    ) {
    }

    public function execute(Observer $observer): void
    {
        $token = $this->guestToken->get();
        $customer = $observer->getEvent()->getCustomer();
        if (!$token || !$customer || !$customer->getId()) {
            return;
        }

        $items = $this->guestWishlist->getItems($token);
        if ($items) {
            $wishlist = $this->wishlistFactory->create()->loadByCustomerId((int) $customer->getId(), true);
            $storeId = (int) $this->storeManager->getStore()->getId();
            foreach ($items as $item) {
                try {
                    $product = $this->productRepository->getById($item['product_id'], false, $storeId);
                    $wishlist->addNewItem($product, new DataObject($item['buy_request']), true);
                } catch (NoSuchEntityException $e) {
                    continue;
                } catch (\Exception $e) {
                    $this->logger->warning('Guest wishlist item not merged: ' . $e->getMessage());
                }
            }
            $wishlist->save();
        }

        $this->guestWishlist->clear($token);
        $this->guestToken->delete();
    }
}
