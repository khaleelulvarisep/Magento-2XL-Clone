<?php

declare(strict_types=1);

namespace Codilar\ObserverExamples\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\InventorySalesApi\Api\GetProductSalableQtyInterface;
use Magento\InventorySalesApi\Api\StockResolverInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\Quote\Model\Quote\Item;
use Magento\Store\Model\StoreManagerInterface;

/**
 * "Hurry, only 3 left!" — after a product is added to the cart, warn the shopper when stock is running low.
 */
class LowStockNoticeOnAddToCart implements ObserverInterface
{
    public function __construct(
        private GetProductSalableQtyInterface $getProductSalableQty,
        private StockResolverInterface $stockResolver,
        private StoreManagerInterface $storeManager,
        private ManagerInterface $messageManager,
        private int $lowStockThreshold = 5
    ) {
    }

    public function execute(Observer $observer): void
    {
        /** @var Item $quoteItem */
        $quoteItem = $observer->getEvent()->getData('quote_item');
        if (!$quoteItem) {
            return;
        }

        // For a configurable product the stock lives on the chosen variant (child item)
        $children = $quoteItem->getChildren();
        $stockItem = $children ? reset($children) : $quoteItem;

        try {
            $websiteCode = $this->storeManager->getWebsite()->getCode();
            $stockId = (int) $this->stockResolver->execute(SalesChannelInterface::TYPE_WEBSITE, $websiteCode)
                ->getStockId();
            $salableQty = $this->getProductSalableQty->execute((string) $stockItem->getSku(), $stockId);
        } catch (\Exception $e) {
            // Products without managed stock (virtual, "Manage Stock = No") — nothing to warn about
            return;
        }

        if ($salableQty > 0 && $salableQty <= $this->lowStockThreshold) {
            $this->messageManager->addNoticeMessage(
                __('Hurry! Only %1 left in stock for "%2".', (int) $salableQty, $quoteItem->getName())
            );
        }
    }
}
