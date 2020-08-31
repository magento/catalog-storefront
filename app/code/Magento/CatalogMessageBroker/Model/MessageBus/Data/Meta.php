<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogMessageBroker\Model\MessageBus\Data;

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
     * @param string|null $scope
     * @param string $eventType
     */
    public function __construct(?string $scope, string $eventType)
    {
        $this->scope = $scope;
        $this->eventType = $eventType;
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
