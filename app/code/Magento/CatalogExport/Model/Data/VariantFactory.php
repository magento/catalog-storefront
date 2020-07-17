<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogExport\Model\Data;

/**
 * Variant factory.
 */
class VariantFactory
{
    /**
     * Object Manager
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Create instance of Variant.
     *
     * @param array $data
     * @return \Magento\CatalogExportApi\Api\Data\VariantInterface
     */
    public function create(array $data)
    {
        return $this->objectManager->create(
            \Magento\CatalogExportApi\Api\Data\VariantInterface::class,
            $data
        );
    }
}