<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SwatchExport\Api;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * @magentoAppIsolation enabled
 */
class ExportTest extends WebapiAbstract
{
    /**
     * @var array
     */
    private $createServiceInfo;

    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    private $objectManager;

    /**
     * @var \Magento\CatalogDataExporter\Model\Feed\Products
     */
    private $productsFeed;

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->productsFeed = $this->objectManager->get(\Magento\CatalogDataExporter\Model\Feed\Products::class);

        $this->createServiceInfo = [
            'rest' => [
                'resourcePath' => '/V1/catalog-export/products',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => 'catalogExportApiProductRepositoryV1',
                'serviceVersion' => 'V1',
                'operation' => 'catalogExportApiProductRepositoryV1Get',
            ],
        ];
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function reindex()
    {
        $appDir = dirname(Bootstrap::getInstance()->getAppTempDir());
        // phpcs:ignore Magento2.Security.InsecureFunction
        exec("php -f {$appDir}/bin/magento indexer:reindex");
    }

    /**
     * @magentoApiDataFixture Magento/Swatches/_files/configurable_product_two_attributes.php
     * @dataProvider attributesResult
     * @param [] $expectedAttributes
     */
    public function testSwatchAttribute($expectedAttributes)
    {
        $this->_markTestAsRestOnly('SOAP will be covered in another test');
        $this->reindex();

        /** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $configurableProductWithSwatches = $productRepository->get('configurable');
        $this->createServiceInfo['rest']['resourcePath'] .= '?ids[0]=' . $configurableProductWithSwatches->getId();
        $results = $this->_webApiCall($this->createServiceInfo, []);

        $swatchAttributes = [];
        if (isset($results[0]['options'])) {
            $options = $results[0]['options'];
            foreach ($options as &$option) {
                // remove option id because it's dynamic field from response
                unset($option['attribute_id']);
                unset($option['id']);
                foreach ($option['values'] as &$value) {
                    unset($value['id']);
                }
            }
            $swatchAttributes = $options;
        }

        $this->assertEquals($expectedAttributes, $swatchAttributes);
    }

    /**
     * Data Provider with eav attribute result
     *
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function attributesResult()
    {
        return [
            'swatch_results_export' => [
                [
                    [
                        'title'  => 'Text swatch attribute',
                        'product_sku'  => null,
                        'render_type'  => null,
                        'type'  => 'super',
                        'values' => [
                            [
                                'price' => null,
                                'sku'   => null,
                                'price_type' => null,
                                'sort_order' => null,
                                'label' => null,
                                'is_default' => null,
                                'sample' => null,
                                'default_label' => 'Option 1',
                                'store_label' => 'Option 1',
                                'value' => null,
                                'quantity' => null,
                                'can_change_quantity' => null,
                                'entity_id' => null,
                            ],
                            [
                                'price' => null,
                                'sku'   => null,
                                'price_type' => null,
                                'sort_order' => null,
                                'label' => null,
                                'is_default' => null,
                                'sample' => null,
                                'default_label' => 'Option 2',
                                'store_label' => 'Option 2',
                                'value' => null,
                                'quantity' => null,
                                'can_change_quantity' => null,
                                'entity_id' => null,
                            ],
                            [
                                'price' => null,
                                'sku'   => null,
                                'price_type' => null,
                                'sort_order' => null,
                                'label' => null,
                                'is_default' => null,
                                'sample' => null,
                                'default_label' => 'Option 3',
                                'store_label' => 'Option 3',
                                'value' => null,
                                'quantity' => null,
                                'can_change_quantity' => null,
                                'entity_id' => null,
                            ],
                        ],
                        'sort_order' => 0,
                        'attribute_code' => 'text_swatch_attribute',
                        'use_default' => false,
                        'is_required' => null
                    ],
                    [
                        'title'  => 'Visual swatch attribute',
                        'product_sku'  => null,
                        'render_type'  => null,
                        'type'  => 'super',
                        'values' => [
                            [
                                'price' => null,
                                'sku'   => null,
                                'price_type' => null,
                                'sort_order' => null,
                                'label' => null,
                                'is_default' => null,
                                'sample' => null,
                                'default_label' => 'option 1',
                                'store_label' => 'option 1',
                                'value' => null,
                                'quantity' => null,
                                'can_change_quantity' => null,
                                'entity_id' => null,
                            ],
                            [
                                'price' => null,
                                'sku'   => null,
                                'price_type' => null,
                                'sort_order' => null,
                                'label' => null,
                                'is_default' => null,
                                'sample' => null,
                                'default_label' => 'option 2',
                                'store_label' => 'option 2',
                                'value' => null,
                                'quantity' => null,
                                'can_change_quantity' => null,
                                'entity_id' => null,
                            ],
                            [
                                'price' => null,
                                'sku'   => null,
                                'price_type' => null,
                                'sort_order' => null,
                                'label' => null,
                                'is_default' => null,
                                'sample' => null,
                                'default_label' => 'option 3',
                                'store_label' => 'option 3',
                                'value' => null,
                                'quantity' => null,
                                'can_change_quantity' => null,
                                'entity_id' => null,
                            ],
                        ],
                        'sort_order' => 0,
                        'attribute_code' => 'visual_swatch_attribute',
                        'use_default' => false,
                        'is_required' => null
                    ],
                ],
            ]
        ];
    }
}