<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogStorefrontStub\Framework\App\Request;

use Magento\Framework\App\ObjectManager;

/**
 * Stub class required to build \Magento\Framework\App\Response\Http and \Magento\Framework\App\Request\Http
 * Do nothing for standalone installation and proxy request to PathInfoProcessor from Store module
 * with monolithic installation
 */
class DefaultPathInfoProcessor implements \Magento\Framework\App\Request\PathInfoProcessorInterface
{
    /**
     * Process pathinfo based on either monolith or standalone installation.
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @param string $pathInfo
     * @return string
     */
    public function process(\Magento\Framework\App\RequestInterface $request, $pathInfo)
    {
        // ad-hoc to install as a monolith. No actual dependency with standalone installation
        $monolithClass = '\Magento' . '\Store\App\Request\PathInfoProcessor';
        if (\class_exists($monolithClass)) {
            return ObjectManager::getInstance()->get($monolithClass)->process($request, $pathInfo);
        }
        return $pathInfo;
    }
}
