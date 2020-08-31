<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogExport\Model\Data;

/**
 * MetaData object for changed entities
 */
class Meta implements MetaInterface
{
    /**
     * @var null|string
     */
    private $scope;

    /**
     * @var string
     */
    private $eventType;

    /**
     * @param string $event_type
     * @param string|null $scope
     */
    public function __construct(string $event_type, string $scope = null)
    {
        $this->scope = $scope;
        $this->eventType = $event_type;
    }

    /**
     * @inheritdoc
     */
    public function getScope(): ?string
    {
        return $this->scope;
    }

    /**
     * @inheritdoc
     */
    public function getEventType(): string
    {
        return $this->eventType;
    }
}
