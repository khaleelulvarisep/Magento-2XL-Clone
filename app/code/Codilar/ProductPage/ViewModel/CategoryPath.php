<?php

declare(strict_types=1);

namespace Codilar\ProductPage\ViewModel;

use Magento\Catalog\Helper\Data as CatalogHelper;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Full category trail for the product (Home / Furniture / Dining / ...), even when the
 * product page is opened directly instead of from a category page.
 */
class CategoryPath implements ArgumentInterface
{
    /** @var Category[]|null */
    private ?array $path = null;

    public function __construct(
        private CatalogHelper $catalogHelper,
        private CollectionFactory $categoryCollectionFactory,
        private StoreManagerInterface $storeManager
    ) {
    }

    /**
     * @return Category[] ordered from top level down
     */
    public function getPath(Product $product): array
    {
        if ($this->path !== null) {
            return $this->path;
        }

        $category = $this->catalogHelper->getCategory() ?: $this->getDeepestCategory($product);
        if (!$category) {
            return $this->path = [];
        }

        $collection = $this->categoryCollectionFactory->create()
            ->setStoreId((int) $this->storeManager->getStore()->getId())
            ->addAttributeToSelect(['name', 'url_key', 'url_path'])
            ->addIdFilter($category->getPathIds())
            ->addFieldToFilter('level', ['gteq' => 2])
            ->addIsActiveFilter()
            ->setOrder('level', 'ASC');

        return $this->path = array_values($collection->getItems());
    }

    /**
     * The product being viewed (for blocks that are not product blocks, e.g. breadcrumbs).
     */
    public function getCurrentProduct(): ?Product
    {
        return $this->catalogHelper->getProduct();
    }

    /**
     * The category the product sits in directly (last item of the trail).
     */
    public function getCategory(Product $product): ?Category
    {
        $path = $this->getPath($product);

        return $path ? end($path) : null;
    }

    private function getDeepestCategory(Product $product): ?Category
    {
        $store = $this->storeManager->getStore();
        $rootPath = '1/' . $store->getRootCategoryId() . '/';

        $collection = $product->getCategoryCollection()
            ->setStoreId((int) $store->getId())
            ->addAttributeToSelect(['name', 'url_key', 'url_path'])
            ->addIsActiveFilter()
            ->addFieldToFilter('path', ['like' => $rootPath . '%'])
            ->setOrder('level', 'DESC')
            ->setPageSize(1);

        $category = $collection->getFirstItem();

        return $category->getId() ? $category : null;
    }
}
