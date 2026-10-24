<?php

declare(strict_types=1);

namespace Codilar\GuestWishlist\ViewModel;

use Codilar\GuestWishlist\Model\GuestToken;
use Codilar\GuestWishlist\Model\GuestWishlist;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Checkout\Helper\Cart as CartHelper;
use Magento\Framework\Data\Helper\PostHelper;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * Data for the guest wishlist page: the products saved under the guest's cookie token.
 */
class GuestWishlistItems implements ArgumentInterface
{
    /** @var Product[]|null */
    private ?array $products = null;

    public function __construct(
        private GuestToken $guestToken,
        private GuestWishlist $guestWishlist,
        private CollectionFactory $productCollectionFactory,
        private ImageHelper $imageHelper,
        private PriceCurrencyInterface $priceCurrency,
        private CartHelper $cartHelper,
        private PostHelper $postHelper,
        private UrlInterface $urlBuilder
    ) {
    }

    /**
     * @return Product[] in the order they were added
     */
    public function getProducts(): array
    {
        if ($this->products !== null) {
            return $this->products;
        }

        $token = $this->guestToken->get();
        $ids = $token ? array_column($this->guestWishlist->getItems($token), 'product_id') : [];
        if (!$ids) {
            return $this->products = [];
        }

        $collection = $this->productCollectionFactory->create()
            ->addIdFilter($ids)
            // special_price + dates are needed, otherwise the final price ignores sales
            ->addAttributeToSelect([
                'name', 'small_image', 'thumbnail', 'url_key',
                'price', 'special_price', 'special_from_date', 'special_to_date',
            ])
            ->addFinalPrice()
            ->addUrlRewrite();

        $byId = $collection->getItems();
        $this->products = array_values(array_filter(array_map(fn (int $id) => $byId[$id] ?? null, $ids)));

        return $this->products;
    }

    public function getImageUrl(Product $product): string
    {
        return $this->imageHelper->init($product, 'category_page_grid')->getUrl();
    }

    public function getFinalPrice(Product $product): string
    {
        return $this->format((float) $product->getPriceInfo()->getPrice('final_price')->getAmount()->getValue());
    }

    /**
     * Regular price when the product is on sale, otherwise null.
     */
    public function getOldPrice(Product $product): ?string
    {
        $priceInfo = $product->getPriceInfo();
        $regular = (float) $priceInfo->getPrice('regular_price')->getAmount()->getValue();
        $final = (float) $priceInfo->getPrice('final_price')->getAmount()->getValue();

        return $regular > $final ? $this->format($regular) : null;
    }

    /**
     * Simple products without required options can go straight to the cart;
     * everything else needs its options chosen on the product page.
     */
    public function canAddToCartDirectly(Product $product): bool
    {
        return $product->isSaleable()
            && in_array($product->getTypeId(), [Type::TYPE_SIMPLE, Type::TYPE_VIRTUAL], true)
            && !$product->getTypeInstance()->hasRequiredOptions($product);
    }

    public function getAddToCartPostJson(Product $product): string
    {
        return $this->postHelper->getPostData($this->cartHelper->getAddUrl($product), ['product' => $product->getId()]);
    }

    public function getRemovePostJson(Product $product): string
    {
        return $this->postHelper->getPostData(
            $this->urlBuilder->getUrl('guestwishlist/index/remove'),
            ['product' => $product->getId()]
        );
    }

    public function getLoginUrl(): string
    {
        return $this->urlBuilder->getUrl('customer/account/login');
    }

    private function format(float $amount): string
    {
        return $this->priceCurrency->convertAndFormat($amount, false);
    }
}
