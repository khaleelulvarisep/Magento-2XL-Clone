<?php

declare(strict_types=1);

namespace Codilar\ProductPage\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Eav\Model\Entity\Attribute\Source\Boolean;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;

/**
 * Adds the product attributes the product page needs, in a "Furniture Details" group:
 * dimensions + material (shown under Specifications), care instructions and warehouse pickup.
 */
class AddFurnitureAttributes implements DataPatchInterface, PatchRevertableInterface
{
    private const GROUP = 'Furniture Details';

    private const SPEC_ATTRIBUTES = [
        'length_cm' => 'Length (cm)',
        'width_cm' => 'Width (cm)',
        'height_cm' => 'Height (cm)',
    ];

    public function __construct(
        private ModuleDataSetupInterface $moduleDataSetup,
        private EavSetupFactory $eavSetupFactory
    ) {
    }

    public function apply(): self
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $sortOrder = 10;
        foreach (self::SPEC_ATTRIBUTES as $code => $label) {
            $eavSetup->addAttribute(Product::ENTITY, $code, $this->specAttribute($label, 'text', $sortOrder += 10) + [
                'frontend_class' => 'validate-number',
            ]);
        }

        $eavSetup->addAttribute(Product::ENTITY, 'material', $this->specAttribute('Material', 'text', 50));

        $eavSetup->addAttribute(Product::ENTITY, 'care_instructions', [
            'type' => 'text',
            'label' => 'Care Instructions',
            'input' => 'textarea',
            'required' => false,
            'global' => Attribute::SCOPE_STORE,
            'visible_on_front' => false,
            'wysiwyg_enabled' => true,
            'is_html_allowed_on_front' => true,
            'user_defined' => true,
            'group' => self::GROUP,
            'sort_order' => 60,
        ]);

        $eavSetup->addAttribute(Product::ENTITY, 'warehouse_pickup', [
            'type' => 'int',
            'label' => 'Eligible for Warehouse Pickup',
            'input' => 'boolean',
            'source' => Boolean::class,
            'default' => '0',
            'required' => false,
            'global' => Attribute::SCOPE_WEBSITE,
            'visible_on_front' => false,
            'used_in_product_listing' => true,
            'user_defined' => true,
            'group' => self::GROUP,
            'sort_order' => 70,
        ]);

        // Show the core "Color" attribute under Specifications as well.
        $eavSetup->updateAttribute(Product::ENTITY, 'color', 'is_visible_on_front', 1);

        return $this;
    }

    public function revert(): void
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        foreach (array_merge(array_keys(self::SPEC_ATTRIBUTES), ['material', 'care_instructions', 'warehouse_pickup']) as $code) {
            $eavSetup->removeAttribute(Product::ENTITY, $code);
        }
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }

    private function specAttribute(string $label, string $input, int $sortOrder): array
    {
        return [
            'type' => 'varchar',
            'label' => $label,
            'input' => $input,
            'required' => false,
            'global' => Attribute::SCOPE_STORE,
            'visible_on_front' => true,
            'user_defined' => true,
            'group' => self::GROUP,
            'sort_order' => $sortOrder,
        ];
    }
}
