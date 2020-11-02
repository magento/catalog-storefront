<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ReviewsMessageBroker\Model;

use Magento\CatalogExport\Event\Data\Entity;

/**
 * Fetch reviews data
 */
interface FetchReviewsInterface
{
    /**
     * Fetch reviews data
     *
     * @param Entity[] $entities
     *
     * @return array
     */
    public function execute(array $entities): array;
}
