<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogStorefront\Model;

use Magento\Framework\MessageQueue\MergedMessageInterfaceFactory;
use Magento\Framework\MessageQueue\MergerInterface;

/**
 * Merges messages from the store front products queue.
 */
class Merger implements MergerInterface
{
    /**
     * @var MergedMessageInterfaceFactory
     */
    private $mergedMessageFactory;

    /**
     * @param MergedMessageInterfaceFactory $mergedMessageFactory
     */
    public function __construct(
        MergedMessageInterfaceFactory $mergedMessageFactory
    ) {
        $this->mergedMessageFactory = $mergedMessageFactory;
    }

    /**
     * @inheritdoc
     */
    public function merge(array $messages): array
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
