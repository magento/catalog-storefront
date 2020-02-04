<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlCache\Controller;

use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\App\Response\HttpInterface as HttpResponse;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\GraphQl\Controller\GraphQl as GraphQlController;
use Magento\GraphQlCache\Model\CacheableQuery;
use Magento\PageCache\Model\Cache\Type as PageCache;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use Magento\GraphQl\AbstractGraphQl;

/**
 * Abstract test class for Graphql cache tests
 */
abstract class AbstractGraphqlCacheTest extends AbstractGraphQl
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @throws LocalizedException
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->enablePageCachePlugin();
        $this->enableCachebleQueryTestProxy();
        parent::setUp();
    }

    /**
     * @return void
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->disableCacheableQueryTestProxy();
        $this->disablePageCachePlugin();
        $this->flushPageCache();
    }

    /**
     * @return void
     */
    protected function enablePageCachePlugin(): void
    {
        /** @var  $registry Registry */
        $registry = $this->objectManager->get(Registry::class);
        $registry->register('use_page_cache_plugin', true, true);
    }

    /**
     * @return void
     */
    protected function disablePageCachePlugin(): void
    {
        /** @var  $registry Registry */
        $registry = $this->objectManager->get(Registry::class);
        $registry->unregister('use_page_cache_plugin');
    }

    /**
     * @return void
     */
    protected function flushPageCache(): void
    {
        /** @var PageCache $fullPageCache */
        $fullPageCache = $this->objectManager->get(PageCache::class);
        $fullPageCache->clean();
    }

    /**
     * Regarding the SuppressWarnings annotation below: the nested class below triggers a false rule match.
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    private function enableCachebleQueryTestProxy(): void
    {
        $cacheableQueryProxy = new class($this->objectManager) extends CacheableQuery {
            /** @var CacheableQuery */
            private $delegate;

            public function __construct(ObjectManager $objectManager)
            {
                $this->reset($objectManager);
            }

            public function reset(ObjectManager $objectManager): void
            {
                $this->delegate = $objectManager->create(CacheableQuery::class);
            }

            public function getCacheTags(): array
            {
                return $this->delegate->getCacheTags();
            }

            public function addCacheTags(array $cacheTags): void
            {
                $this->delegate->addCacheTags($cacheTags);
            }

            public function isCacheable(): bool
            {
                return $this->delegate->isCacheable();
            }

            public function setCacheValidity(bool $cacheable): void
            {
                $this->delegate->setCacheValidity($cacheable);
            }

            public function shouldPopulateCacheHeadersWithTags(): bool
            {
                return $this->delegate->shouldPopulateCacheHeadersWithTags();
            }
        };
        $this->objectManager->addSharedInstance($cacheableQueryProxy, CacheableQuery::class);
    }

    /**
     * @return void
     */
    private function disableCacheableQueryTestProxy(): void
    {
        $this->resetQueryCacheTags();
        $this->objectManager->removeSharedInstance(CacheableQuery::class);
    }

    /**
     * @return void
     */
    protected function resetQueryCacheTags(): void
    {
        $this->objectManager->get(CacheableQuery::class)->reset($this->objectManager);
    }

    /**
     * @param array $queryParams
     * @return HttpResponse
     */
    protected function dispatchGraphQlGETRequest(array $queryParams): HttpResponse
    {
        $this->resetQueryCacheTags();

        /** @var HttpRequest $request */
        $request = $this->objectManager->get(HttpRequest::class);
        $request->setPathInfo('/graphql');
        $request->setMethod('GET');
        $request->setParams($queryParams);

        // required for \Magento\Framework\App\PageCache\Identifier to generate the correct cache key
        $request->setUri(implode('?', [$request->getPathInfo(), http_build_query($queryParams)]));

        return $this->objectManager->create(GraphQlController::class)->dispatch($request);
    }
}
