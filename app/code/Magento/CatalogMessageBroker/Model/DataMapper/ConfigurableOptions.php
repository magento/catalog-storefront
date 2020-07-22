<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogMessageBroker\Model\DataMapper;

use Magento\CatalogExportApi\Api\Data\CustomOption;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

/**
 * Data mapper for configurable options
 */
class ConfigurableOptions implements DataMapperInterface
{
    /**
     * @inheritDoc
     */
    public function map(array $data): array
    {
        $configurableOptions = [];
        if ($data['type'] === Configurable::TYPE_CODE && !empty($data['options'])) {
            foreach ($data['options'] as $optionArray) {
                if ($optionArray['type'] === CustomOption::SUPER_OPTION_TYPE) {
                    $configurableOptions[$optionArray['id']] = [
                        'id' => $optionArray['id'],
                        'type' => $optionArray['type'],
                        'label' => $optionArray['label'],
                        'position' => $optionArray['sort_order'],
                        'product_id' => $data['id']
                    ];

                    $configurableOptions[$optionArray['id']]['values'] = $this->mapOptionValues($optionArray);
                }
            }
        }
        return $configurableOptions;
    }

    /**
     * Map option values
     *
     * @param $optionArray
     * @return array|null
     */
    private function mapOptionValues(?array $optionArray): array
    {
        $values = [];
        if (!empty($optionArray['values'])) {
            foreach ($optionArray['values'] as $value) {
                $values[$value['id']] = [
                    'value_index' => $value['id'],
                    'label' => $value['label']
                ];
            }
        }
        return $values;
    }
}
