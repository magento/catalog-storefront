<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ReviewsMessageBroker\Model;

use Magento\CatalogExport\Event\Data\Entity;

/**
 * Fetch ratings metadata
 */
interface FetchRatingsMetadataInterface
{
    /**
     * Fetch ratings metadata
     *
     * @param Entity[] $entities
     * @param string $scope
     *
     * @return array
     */
    public function execute(array $entities, string $scope): array;
}
