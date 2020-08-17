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
     * @var string
     */
    private $scope;

    /**
     * @var string
     */
    private $eventType;

    /**
     * @inheritdoc
     */
    public function setScope(?string $scope): void
    {
        $this->scope = $scope;
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
    public function setEventType(string $eventType): void
    {
        $this->eventType = $eventType;
    }

    /**
     * @inheritdoc
     */
    public function getEventType(): string
    {
        return $this->eventType;
    }
}
