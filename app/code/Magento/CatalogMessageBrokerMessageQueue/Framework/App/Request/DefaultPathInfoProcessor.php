<?php
/**
 * PATH_INFO processor
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogMessageBrokerMessageQueue\Framework\App\Request;

use Magento\Framework\App\Request\PathInfoProcessorInterface;

/**
 * @api
 * @since 100.0.2
 */
class DefaultPathInfoProcessor implements PathInfoProcessorInterface
{
    /**
     * Do not process pathinfo
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @param string $pathInfo
     * @return string
     */
    public function process(\Magento\Framework\App\RequestInterface $request, $pathInfo)
    {
        return $pathInfo;
    }
}
