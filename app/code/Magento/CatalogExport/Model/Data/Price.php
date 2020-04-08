<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogExport\Model\Data;

use Magento\CatalogExport\Api\Data\PriceInterface;
use Magento\Framework\Model\AbstractModel;

class Price extends AbstractModel implements PriceInterface
{
    private const REGULAR_PRICE = 'regular_price';

    private const FINAL_PRICE = 'final_price';

    /**
     * @inheritdoc
     */
    public function getRegularPrice() :? float
    {
        return $this->getData(self::REGULAR_PRICE);
    }

    /**
     * @inheritdoc
     */
    public function setRegularPrice($regularPrice)
    {
        $this->setData(self::REGULAR_PRICE, $regularPrice);
    }

    /**
     * @inheritdoc
     */
    public function getFinalPrice() :? float
    {
        return $this->getData(self::FINAL_PRICE);
    }

    /**
     * @inheritdoc
     */
    public function setFinalPrice($finalPrice)
    {
        $this->setData(self::FINAL_PRICE, $finalPrice);
    }
}