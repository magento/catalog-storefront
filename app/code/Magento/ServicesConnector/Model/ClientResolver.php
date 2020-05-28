<?php
namespace Magento\ServicesConnector\Model;

use Magento\ServicesConnector\Api\KeyNotFoundException;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\UrlInterface;
use Magento\ServicesConnector\Api\ClientResolverInterface;

/**
 * Client resolver implementation
 */
class ClientResolver implements ClientResolverInterface
{
    /**
     * @var GuzzleClientFactory
     */
    private $clientFactory;

    /**
     * @var EnvironmentFactory
     */
    private $environmentFactory;

    /**
     * @var \Magento\Framework\App\ProductMetadata
     */
    private $productMetadata;

    /**
     * ClientResolver constructor.
     * @param GuzzleClientFactory $clientFactory
     * @param EnvironmentFactory $environmentFactory
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     */
    public function __construct(
        GuzzleClientFactory $clientFactory,
        EnvironmentFactory $environmentFactory,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata
    ) {
        $this->clientFactory = $clientFactory;
        $this->environmentFactory = $environmentFactory;
        $this->productMetadata = $productMetadata;
    }

    /**
     * @inheritDoc
     */
    public function createHttpClient($extension, $environment = 'production')
    {
        $environment = $this->environmentFactory->create($environment);

        return $this->clientFactory->create([
                'base_uri' => $environment->getGatewayUrl($environment),
                'http_errors' => false,
                'headers' => [
                    'magento-api-key' => $environment->getApiKey($extension),
                    'User-Agent' => sprintf(
                        'Magento Services Connector (Magento: %s)',
                        $this->productMetadata->getEdition() . ' '
                        . $this->productMetadata->getVersion()
                    )
                ]
            ]);
    }
}
