<?php
namespace Magento\ServicesConnector\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Client resolver implementation
 */
class Environment
{
    const PROD_GATEWAY_URL_PATH = 'services_connector/{env}_gateway_url';
    const API_KEY_PATH = 'services_connector/services_connector_integration/{env}_api_key';

    /**
     * @var string
     */
    private $environment;
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Environment constructor.
     *
     * @param string $environment
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        $environment,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->environment = $environment;
        $this->scopeConfig = $scopeConfig;
    }

    public function getGatewayUrl()
    {
        return $this->scopeConfig->getValue(str_replace(
            '{env}',
            $this->environment,
            self::PROD_GATEWAY_URL_PATH
        ));
    }

    /**
     * One key per environment so far
     *
     * @param string $extension
     * @return string
     */
    public function getApiKey($extension)
    {
        return $this->scopeConfig->getValue(str_replace(
            '{env}',
            $this->environment,
            self::API_KEY_PATH
        ));
    }

}
