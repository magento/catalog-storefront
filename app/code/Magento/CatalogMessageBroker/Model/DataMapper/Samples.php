<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogMessageBroker\Model\DataMapper;

/**
 * Data mapper for download samples
 */
class Samples implements DataMapperInterface
{
    /**
     * @inheritDoc
     */
    public function map(array $data): array
    {
        if (empty($data['samples'])) {
            return [];
        }

        return [
            'samples' => \array_map(function ($sampleData) {
                return \array_merge([
                    'url' => $sampleData['url'],
                    'label' => $sampleData['label'],
                    'sort_order' => $sampleData['sort_order'],
                ]);
            }, $data['samples']),
        ];
    }
}
