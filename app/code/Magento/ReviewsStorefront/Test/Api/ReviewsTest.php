<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\ReviewsStorefront\Test\Api;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogStorefrontApi\Api\Data\CustomerProductReviewRequestInterface;
use Magento\CatalogStorefrontApi\Api\Data\ProductReviewCountRequestInterface;
use Magento\CatalogStorefrontApi\Api\Data\ProductReviewRequestInterface;
use Magento\CatalogStorefrontApi\Api\Data\RatingMetadataArrayMapper;
use Magento\CatalogStorefrontApi\Api\Data\RatingsMetadataRequestInterface;
use Magento\CatalogStorefrontApi\Api\Data\ReadReviewArrayMapper;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\ReviewsStorefront\Model\ProductReviewsServer;
use Magento\ReviewsStorefront\Model\RatingsMetadataServer;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\ExpectationFailedException;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

/**
 * Test for product reviews data
 */
class ReviewsTest extends StorefrontTestsAbstract
{
    /**
     * @var ProductReviewsServer
     */
    private $reviewService;

    /**
     * @var ProductReviewRequestInterface
     */
    private $productReviewRequest;

    /**
     * @var CustomerProductReviewRequestInterface
     */
    private $customerReviewRequest;

    /**
     * @var ReadReviewArrayMapper
     */
    private $productReviewArrayMapper;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var RatingsMetadataServer
     */
    private $ratingService;

    /**
     * @var RatingsMetadataRequestInterface
     */
    private $ratingMetadataRequest;

    /**
     * @var RatingMetadataArrayMapper
     */
    private $ratingMetadataArrayMapper;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var ProductReviewCountRequestInterface
     */
    private $productReviewCountRequest;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->reviewService = Bootstrap::getObjectManager()->create(ProductReviewsServer::class);
        $this->ratingService = Bootstrap::getObjectManager()->create(RatingsMetadataServer::class);
        $this->ratingMetadataRequest = Bootstrap::getObjectManager()->create(RatingsMetadataRequestInterface::class);
        $this->ratingMetadataArrayMapper = Bootstrap::getObjectManager()->create(RatingMetadataArrayMapper::class);
        $this->productReviewRequest = Bootstrap::getObjectManager()->create(ProductReviewRequestInterface::class);
        $this->customerReviewRequest = Bootstrap::getObjectManager()->create(
            CustomerProductReviewRequestInterface::class
        );
        $this->productReviewCountRequest = Bootstrap::getObjectManager()->create(
            ProductReviewCountRequestInterface::class
        );
        $this->customerRepository = Bootstrap::getObjectManager()->create(CustomerRepositoryInterface::class);
        $this->productReviewArrayMapper = Bootstrap::getObjectManager()->create(ReadReviewArrayMapper::class);
        $this->productRepository = Bootstrap::getObjectManager()->create(ProductRepositoryInterface::class);
    }

    /**
     * Validate product reviews data
     *
     * @param array $reviewsDataProvider
     *
     * @magentoDataFixture Magento/Review/_files/customer_review_with_rating.php
     * @dataProvider getReviewsDataProvider
     *
     * @magentoDbIsolation disabled
     *
     * @return void
     *
     * @throws NoSuchEntityException
     * @throws ExpectationFailedException
     * @throws InvalidArgumentException
     * @throws \Throwable
     */
    public function testProductReviewsData(array $reviewsDataProvider): void
    {
        $product = $this->productRepository->get('simple');

        $this->productReviewRequest->setProductId((string)$product->getId());
        $this->productReviewRequest->setStore('default');

        $items = $this->reviewService->GetProductReviews($this->productReviewRequest)->getItems();
        self::assertNotEmpty($items);
        $item = \array_shift($items);
        $actualData = $this->productReviewArrayMapper->convertToArray($item);

        $this->validateReviewData($actualData, $reviewsDataProvider);
    }

    /**
     * Validate customer reviews data
     *
     * @param array $reviewsDataProvider
     *
     * @magentoDataFixture Magento/Review/_files/customer_review_with_rating.php
     * @dataProvider getReviewsDataProvider
     *
     * @magentoDbIsolation disabled
     *
     * @return void
     *
     * @throws NoSuchEntityException
     * @throws ExpectationFailedException
     * @throws InvalidArgumentException
     * @throws \Throwable
     */
    public function testCustomerReviewsData(array $reviewsDataProvider): void
    {
        $customer = $this->customerRepository->get('customer@example.com');

        $this->customerReviewRequest->setCustomerId((string)$customer->getId());
        $this->customerReviewRequest->setStore('default');

        $items = $this->reviewService->GetCustomerProductReviews($this->customerReviewRequest)->getItems();
        self::assertNotEmpty($items);
        $item = \array_shift($items);
        $actualData = $this->productReviewArrayMapper->convertToArray($item);

        $this->validateReviewData($actualData, $reviewsDataProvider);
    }

    /**
     * Validate review and rating data
     *
     * @param array $actualData
     * @param array $expectedData
     *
     * @return void
     *
     * @throws ExpectationFailedException
     * @throws InvalidArgumentException
     * @throws AssertionFailedError
     * @throws \Throwable
     */
    private function validateReviewData(array $actualData, array $expectedData): void
    {
        $ratingIds = \array_map(function ($rating) {
            return $rating['id'];
        }, $actualData['ratings']);

        $this->ratingMetadataRequest->setRatingIds($ratingIds);
        $this->ratingMetadataRequest->setStore('default');

        $ratingItems = $this->ratingService->GetRatingsMetadata($this->ratingMetadataRequest)->getItems();
        self::assertNotEmpty($ratingItems);

        $ratingNames = [];
        foreach ($ratingItems as $ratingItem) {
            $ratingNames[$ratingItem->getRatingId()] = $ratingItem->getName();
        }

        foreach ($actualData['ratings'] as &$rating) {
            $rating['name'] = $ratingNames[$rating['id']];
        }

        $this->compare($expectedData, $actualData);
    }

    /**
     * Retrieve reviews data provider
     *
     * @return array
     */
    public function getReviewsDataProvider(): array
    {
        return [
            'reviewData' => [
                'item' => [
                    'product_id' => '1',
                    'title' => 'Review Summary',
                    'nickname' => 'Nickname',
                    'text' => 'Review text',
                    'ratings' => [
                        [
                            'name' => 'Quality',
                            'value' => '2',
                        ],
                        [
                            'name' => 'Value',
                            'value' => '2',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Validate product review count data
     *
     * @magentoDataFixture Magento/Review/_files/different_reviews.php
     * @magentoDbIsolation disabled
     *
     * @return void
     *
     * @throws NoSuchEntityException
     * @throws ExpectationFailedException
     * @throws InvalidArgumentException
     * @throws \Throwable
     */
    public function testProductReviewCount(): void
    {
        $product = $this->productRepository->get('simple');

        $this->productReviewCountRequest->setProductId((string)$product->getId());
        $this->productReviewCountRequest->setStore('default');

        $reviewCount = $this->reviewService->GetProductReviewCount($this->productReviewCountRequest)->getReviewCount();
        self::assertEquals(2, $reviewCount);
    }
}
