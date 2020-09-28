<?php
declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Grpc\Model;

use Magento\Grpc\Api\GreetingRequest;
use Magento\Grpc\Api\GreetingReply;
use Spiral\GRPC;

/**
 * Greet the person
 *
 * @package Magento\Grpc\Model
 */
class Greeting implements \Magento\Grpc\Api\GreetingInterface
{
    /**
     * @param GRPC\ContextInterface $ctx
     * @param GreetingRequest $in
     * @return GreetingReply
     *
     * @throws GRPC\Exception\InvokeException
     */
    public function Greet(GRPC\ContextInterface $ctx, GreetingRequest $in): GreetingReply
    {
        return new GreetingReply(['greeting' => 'Hello ' . $in->getName()]);
    }
}
