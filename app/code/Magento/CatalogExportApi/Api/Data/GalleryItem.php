<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogExportApi\Api\Data;

/**
 * Class GalleryItem
 */
class GalleryItem
{
    /**
     * @var string
     */
    private $url;

    /**
     * @var string
     */
    private $label;

    /**
     * @var string
     */
    private $mediaType;

    /**
     * @var string[]|null
     */
    private $types;

    /**
     * @var string|null
     */
    private $description;

    /**
     * @var int
     */
    private $sortOrder;

    /**
     * @var \Magento\CatalogExportApi\Api\Data\VideoAttributes|null
     */
    private $videoAttributes;

    /**
     * Get URL
     *
     * @return string
     */
    public function getUrl() : string
    {
        return $this->url;
    }

    /**
     * Set URL
     *
     * @param string $url
     *
     * @return void
     */
    public function setUrl(string $url) : void
    {
        $this->url = $url;
    }

    /**
     * Get label
     *
     * @return string
     */
    public function getLabel() : string
    {
        return $this->label;
    }

    /**
     * Set label
     *
     * @param string $label
     *
     * @return void
     */
    public function setLabel(string $label) : void
    {
        $this->label = $label;
    }

    /**
     * Get media type
     *
     * @return string
     */
    public function getMediaType() : string
    {
        return $this->mediaType;
    }

    /**
     * Set media type
     *
     * @param string $mediaType
     *
     * @return void
     */
    public function setMediaType(string $mediaType) : void
    {
        $this->mediaType = $mediaType;
    }

    /**
     * Get types
     *
     * @return string[]|null
     */
    public function getTypes() : ?array
    {
        return $this->types;
    }

    /**
     * Set types
     *
     * @param string[]|null $types
     *
     * @return void
     */
    public function setTypes(?array $types) : void
    {
        $this->types = $types;
    }

    /**
     * Get description
     *
     * @return string|null
     */
    public function getDescription() : ?string
    {
        return $this->description;
    }

    /**
     * Set description
     *
     * @param string|null $description
     *
     * @return void
     */
    public function setDescription(?string $description) : void
    {
        $this->description = $description;
    }

    /**
     * Get sort order
     *
     * @return int
     */
    public function getSortOrder() : int
    {
        return $this->sortOrder;
    }

    /**
     * Set sort order
     *
     * @param int $sortOrder
     *
     * @return void
     */
    public function setSortOrder(int $sortOrder) : void
    {
        $this->sortOrder = $sortOrder;
    }

    /**
     * Get video attributes
     *
     * @return \Magento\CatalogExportApi\Api\Data\VideoAttributes|null
     */
    public function getVideoAttributes() : ?VideoAttributes
    {
        return $this->videoAttributes;
    }

    /**
     * Set video attributes
     *
     * @param \Magento\CatalogExportApi\Api\Data\VideoAttributes|null $videoAttributes
     *
     * @return void
     */
    public function setVideoAttributes(?VideoAttributes $videoAttributes) : void
    {
        $this->videoAttributes = $videoAttributes;
    }
}
