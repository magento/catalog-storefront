<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogMessageBroker\Model;

use Magento\CatalogExportApi\Api\VariantRepositoryInterface;

/**
 * Serializer implementation for ids.
 */
class IdentificatorSerializer implements SerializerInterface
{
    public function serialize(array $ids): string
    {
        return json_encode($ids);
    }

    public function deserialize(string $ids): array
    {
        return json_decode($ids, true);
    }
}
