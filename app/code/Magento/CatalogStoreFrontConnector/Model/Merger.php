<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogStoreFrontConnector\Model;

use Magento\Framework\MessageQueue\MergerInterface;

/**
 * Merges messages from the store front products queue.
 */
class Merger implements MergerInterface
{
    /**
     * @var \Magento\Framework\MessageQueue\MergedMessageInterfaceFactory
     */
    private $mergedMessageFactory;
    /**
     * @var ReindexProductsDataInterfaceFactory
     */
    private $reindexProductsDataFactory;

    /**
     * @param ReindexProductsDataInterfaceFactory $reindexProductsDataFactory
     * @param \Magento\Framework\MessageQueue\MergedMessageInterfaceFactory $mergedMessageFactory
     */
    public function __construct(
        ReindexProductsDataInterfaceFactory $reindexProductsDataFactory,
        \Magento\Framework\MessageQueue\MergedMessageInterfaceFactory $mergedMessageFactory
    ) {
        $this->mergedMessageFactory = $mergedMessageFactory;
        $this->reindexProductsDataFactory = $reindexProductsDataFactory;
    }

    /**
     * @inheritdoc
     */
    public function merge(array $messages)
    {
        $result = [];

        foreach ($messages as $topicName => $topicMessages) {
            $messagesIds = array_keys($topicMessages);
            $result[$topicName][] = $this->mergedMessageFactory->create(
                [
                    'mergedMessage' => $topicMessages,
                    'originalMessagesIds' => $messagesIds
                ]
            );
        }

        return $result;
    }
}
