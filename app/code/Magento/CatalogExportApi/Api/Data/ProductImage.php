<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogExportApi\Api\Data;

/**
 * Product image entity
 */
class ProductImage
{
    /**
     * @var string|null
     */
    private $url;

    /**
     * @var string|null
     */
    private $label;

    /**
     * @param string|null $url
     * @param string|null $label
     */
    public function __construct(
        ?string $url,
        ?string $label
    ) {
        $this->url = $url;
        $this->label = $label;
    }

    /**
     * Get product image url
     *
     * @return string|null
     */
    public function getUrl() : ?string
    {
        return $this->url;
    }

    /**
     * Get product image label
     *
     * @return string|null
     */
    public function getLabel() : ?string
    {
        return $this->label;
    }
}
