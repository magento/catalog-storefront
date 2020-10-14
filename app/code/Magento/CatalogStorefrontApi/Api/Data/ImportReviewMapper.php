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
 * Autogenerated description for ImportReview class
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.UnusedPrivateField)
 * @SuppressWarnings(PHPMD.NPathComplexity)
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
final class ImportReviewMapper
{
    /**
     * @var string
     */
    private static $dtoClassName = ImportReviewInterface::class;

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
    * @return ImportReview
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
    * @param ImportReview $dto
    * @param string $key
    * @param mixed $value
    */
    private function setByKey(ImportReview $dto, string $key, $value): void
    {
        switch ($key) {
            case "review_id":
                $dto->setReviewId((string) $value);
                break;
            case "product_id":
                $dto->setProductId((string) $value);
                break;
            case "title":
                $dto->setTitle((string) $value);
                break;
            case "nickname":
                $dto->setNickname((string) $value);
                break;
            case "text":
                $dto->setText((string) $value);
                break;
            case "customer_id":
                $dto->setCustomerId((string) $value);
                break;
            case "visibility":
                $dto->setVisibility((array) $value);
                break;
            case "ratings":
                $convertedArray = [];
                foreach ($value as $element) {
                    $convertedArray[] = $this->objectManager
                        ->create(\Magento\CatalogStorefrontApi\Api\Data\RatingMapper::class)
                        ->setData($element)
                        ->build();
                }
                $dto->setRatings($convertedArray);
                break;
        }
    }
}
