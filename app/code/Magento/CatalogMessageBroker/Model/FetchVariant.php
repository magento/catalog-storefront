<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogMessageBroker\Model;

use Magento\CatalogExportApi\Api\VariantRepositoryInterface;

/**
 * Fetch Product Variants implementation.
 */
class FetchVariant implements FetchVariantInterface
{
    /**
     * @var VariantRepositoryInterface
     */
    private $variantRepository;

    /**
     * @param VariantRepositoryInterface $variantRepository
     */
    public function __construct(VariantRepositoryInterface $variantRepository)
    {
        $this->variantRepository = $variantRepository;
    }

    /**
     * @inheritdoc
     */
    public function execute(array $ids)
    {
        return $this->variantRepository->get($ids);
    }
}
