<?php
namespace Magento\ServicesConnector\Model;

use Magento\ServicesConnector\Api\ClientResolverInterface;
use Magento\ServicesConnector\Api\KeyNotFoundException;
use Magento\ServicesConnector\Api\KeyValidationInterface;

/**
 * Client resolver implementation
 */
class KeyValidation implements KeyValidationInterface
{
    /**
     * @var EnvironmentFactory
     */
    private $environmentFactory;
    /**
     * @var ClientResolverInterface
     */
    private $clientResolver;

    /**
     * KeyValidation constructor.
     *
     * @param EnvironmentFactory $environmentFactory
     * @param ClientResolverInterface $clientResolver
     */
    public function __construct(
        EnvironmentFactory $environmentFactory,
        ClientResolverInterface $clientResolver
    ) {
        $this->environmentFactory = $environmentFactory;
        $this->clientResolver = $clientResolver;
    }

    /**
     * @inheritDoc
     */
    public function execute($extension, $environment = 'production')
    {
        $envObject = $this->environmentFactory->create($environment);
        if (empty($envObject->getApiKey($extension))) {
            throw new KeyNotFoundException("Api key is not found for extension '$extension'");
        }
        $client = $this->clientResolver->createHttpClient($extension, $environment);
        $result = $client->request('GET', '/gateway/apikeycheck');

        if ($result->getStatusCode() >= 400 && $result->getStatusCode() < 600) {
            return false;
        }

        return true;
    }
}
