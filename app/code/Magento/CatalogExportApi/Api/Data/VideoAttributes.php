<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogExportApi\Api\Data;

/**
 * Class VideoAttributes
 */
class VideoAttributes
{
    /**
     * @var string|null
     */
    private $mediaType;

    /**
     * @var string|null
     */
    private $videoProvider;

    /**
     * @var string|null
     */
    private $videoUrl;

    /**
     * @var string|null
     */
    private $videoTitle;

    /**
     * @var string|null
     */
    private $videoDescription;

    /**
     * @var string|null
     */
    private $videoMetadata;

    /**
     * VideoAttributes constructor.
     *
     * @param string|null $mediaType
     * @param string|null $videoProvider
     * @param string|null $videoUrl
     * @param string|null $videoTitle
     * @param string|null $videoDescription
     * @param string|null $videoMetadata
     */
    public function __construct(
        ?string $mediaType,
        ?string $videoProvider,
        ?string $videoUrl,
        ?string $videoTitle,
        ?string $videoDescription,
        ?string $videoMetadata
    ) {
        $this->mediaType = $mediaType;
        $this->videoProvider = $videoProvider;
        $this->videoUrl = $videoUrl;
        $this->videoTitle = $videoTitle;
        $this->videoDescription = $videoDescription;
        $this->videoMetadata = $videoMetadata;
    }

    /**
     * Get media type
     *
     * @return string|null
     */
    public function getMediaType() : ?string
    {
        return $this->mediaType;
    }

    /**
     * Get video provider
     *
     * @return string|null
     */
    public function getVideoProvider() : ?string
    {
        return $this->videoProvider;
    }

    /**
     * Get video URL
     *
     * @return string|null
     */
    public function getVideoUrl() : ?string
    {
        return $this->videoUrl;
    }

    /**
     * Get video title
     *
     * @return string|null
     */
    public function getVideoTitle() : ?string
    {
        return $this->videoTitle;
    }

    /**
     * Get video description
     *
     * @return string|null
     */
    public function getVideoDescription() : ?string
    {
        return $this->videoDescription;
    }

    /**
     * Get video metadata
     *
     * @return string|null
     */
    public function getVideoMetadata() : ?string
    {
        return $this->videoMetadata;
    }
}
