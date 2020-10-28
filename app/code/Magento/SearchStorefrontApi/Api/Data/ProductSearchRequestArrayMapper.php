<?php
# Generated by the Magento PHP proto generator.  DO NOT EDIT!

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SearchStorefrontApi\Api\Data;

use Magento\Framework\ObjectManagerInterface;

/**
 * Autogenerated description for ProductSearchRequest class
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.UnusedPrivateField)
 * @SuppressWarnings(PHPMD.NPathComplexity)
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
final class ProductSearchRequestArrayMapper
{
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
    * Convert the DTO to the array with the data
    *
    * @param ProductSearchRequest $dto
    * @return array
    */
    public function convertToArray(ProductSearchRequest $dto)
    {
        $result = [];
        $result["phrase"] = $dto->getPhrase();
        $result["store"] = $dto->getStore();
        $result["customerGroupId"] = $dto->getCustomerGroupId();
        $result["page_size"] = $dto->getPageSize();
        $result["current_page"] = $dto->getCurrentPage();
        /** Convert complex Array field **/
        $fieldArray = [];
        foreach ($dto->getFilters() as $fieldArrayDto) {
            $fieldArray[] = $this->objectManager->get(\Magento\SearchStorefrontApi\Api\Data\FilterArrayMapper::class)
                ->convertToArray($fieldArrayDto);
        }
        $result["filters"] = $fieldArray;
        /** Convert complex Array field **/
        $fieldArray = [];
        foreach ($dto->getSort() as $fieldArrayDto) {
            $fieldArray[] = $this->objectManager->get(\Magento\SearchStorefrontApi\Api\Data\SortArrayMapper::class)
                ->convertToArray($fieldArrayDto);
        }
        $result["sort"] = $fieldArray;
        return $result;
    }
}
