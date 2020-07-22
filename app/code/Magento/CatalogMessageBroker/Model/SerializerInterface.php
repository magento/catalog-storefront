<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogMessageBroker\Model;

/**
 * Serializer to message transport format.
 */
interface SerializerInterface
{
    /**
     * Serialize data to message format.
     *
     * @param array $data
     * @return string
     */
    public function serialize(array $data): string;

    /**
     * Deserialize message.
     *
     * @param string $data
     * @return array
     */
    public function deserialize(string $data): array;
}
