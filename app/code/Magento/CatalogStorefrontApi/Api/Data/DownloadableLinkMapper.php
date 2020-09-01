<?php
# Generated by the Magento PHP proto generator.  DO NOT EDIT!

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogStorefrontApi\Api\Data;

use Magento\Framework\ObjectManagerInterface;

/**
 * Autogenerated description for DownloadableLink class
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.UnusedPrivateField)
 * @SuppressWarnings(PHPMD.NPathComplexity)
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
final class DownloadableLinkMapper
{
    /**
     * @var string
     */
    private static $dtoClassName = DownloadableLinkInterface::class;

    /**
     * @var mixed
     */
    private $data;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
    * Set the data to populate the DTO
    *
    * @param mixed $data
    * @return $this
    */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    /**
    * Build new DTO populated with the data
    *
    * @return DownloadableLink
    */
    public function build()
    {
        $dto = $this->objectManager->create(self::$dtoClassName);
        foreach ($this->data as $key => $valueData) {
            if ($valueData === null) {
                continue;
            }
            $this->setByKey($dto, $key, $valueData);
        }
        return $dto;
    }

    /**
    * Set the value of the key using setters.
    *
    * In case if the field is object, the corresponding Mapper would be create and DTO representing the field data
    * would be built
    *
    * @param DownloadableLink $dto
    * @param string $key
    * @param mixed $value
    */
    private function setByKey(DownloadableLink $dto, string $key, $value): void
    {
        switch ($key) {
            case "sample_url":
                $dto->setSampleUrl((string) $value);
                break;
            case "title":
                $dto->setTitle((string) $value);
                break;
            case "sort_order":
                $dto->setSortOrder((int) $value);
                break;
            case "sample_type":
                $dto->setSampleType((string) $value);
                break;
            case "sample_file":
                $dto->setSampleFile((string) $value);
                break;
            case "link_id":
                $dto->setLinkId((int) $value);
                break;
            case "price":
                $dto->setPrice((float) $value);
                break;
            case "link_type":
                $dto->setLinkType((string) $value);
                break;
            case "is_shareable":
                $dto->setIsShareable((bool) $value);
                break;
            case "number_of_downloads":
                $dto->setNumberOfDownloads((int) $value);
                break;
            case "entity_id":
                $dto->setEntityId((string) $value);
                break;
        }
    }
}
