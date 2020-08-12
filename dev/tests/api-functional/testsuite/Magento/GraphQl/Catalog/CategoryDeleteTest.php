<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Catalog;

use Magento\Catalog\Model\CategoryRepository;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test loading of category tree
 */
class CategoryDeleteTest extends GraphQlAbstract
{
    /**
     * @var CategoryRepository
     */
    private $categoryRepository;

    protected function setUp(): void
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->categoryRepository = $objectManager->get(CategoryRepository::class);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/categories.php
     */
    public function testQueryCategoryAfterDelete()
    {
        $categoryId = 13;
        $query = <<<QUERY
{
  category(id: {$categoryId}) {
      id
      name
  }
}
QUERY;
        $response = $this->graphQlQuery($query);
        self::assertEquals('Category 1.2', $response['category']['name']);
        self::assertEquals(13, $response['category']['id']);

        $registry = ObjectManager::getInstance()->get(\Magento\Framework\Registry::class);
        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', true);
        $this->categoryRepository->deleteByIdentifier($categoryId);
        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', false);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Category doesn\'t exist');

        $this->graphQlQuery($query);
    }
}
