<?php
namespace Magento\ServicesConnector\Model;

use Magento\ServicesConnector\Api\ConfigInterface;
use Magento\ServicesConnector\Api\KeyNotFoundException;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\UrlInterface;

/**
 * Client resolver implementation
 */
class Config implements ConfigInterface
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * Config constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param UrlInterface $url
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        UrlInterface $url
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->url = $url;
    }

    /**
     * @inheritDoc
     */
    public function getKeyConfigPage($extension, $environment = 'production')
    {
        return $this->url->getUrl(
            'adminhtml/system_config/edit',
            [
                'section' => 'services_connector'
            ]
        );
    }

    /**
     * @inheritDoc
     */
    public function getApiPortalUrl()
    {
        return $this->scopeConfig->getValue('services_connector/api_portal_url');
    }
}
