<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\TaxProductConnector\Business\Product;

use Generated\Shared\Transfer\ProductAbstractTransfer;
use Spryker\Zed\TaxProductConnector\Business\Exception\TaxSetNotFoundException;
use Spryker\Zed\TaxProductConnector\Persistence\TaxProductConnectorQueryContainerInterface;

class ProductAbstractTaxSetMapper
{
    /**
     * @var \Spryker\Zed\TaxProductConnector\Persistence\TaxProductConnectorQueryContainerInterface
     */
    protected $queryContainer;

    /**
     * @param \Spryker\Zed\TaxProductConnector\Persistence\TaxProductConnectorQueryContainerInterface $queryContainer
     */
    public function __construct(TaxProductConnectorQueryContainerInterface $queryContainer)
    {
        $this->queryContainer = $queryContainer;
    }

    /**
     * @param \Generated\Shared\Transfer\ProductAbstractTransfer $productAbstractTransfer
     *
     * @throws \Spryker\Zed\TaxProductConnector\Business\Exception\TaxSetNotFoundException
     *
     * @return \Generated\Shared\Transfer\ProductAbstractTransfer
     */
    public function mapTaxSet(ProductAbstractTransfer $productAbstractTransfer)
    {
        $productAbstractTransfer->requireIdProductAbstract();

        $taxSetEntity = $this->queryContainer
            ->queryTaxSetForProductAbstract($productAbstractTransfer->getIdProductAbstract())
            ->findOne();

        if ($taxSetEntity === null) {
            throw new TaxSetNotFoundException(
                sprintf(
                    'Tax set for product abstract with id "%d" not found.',
                    $productAbstractTransfer->getIdProductAbstract(),
                ),
            );
        }

        $productAbstractTransfer->setIdTaxSet($taxSetEntity->getIdTaxSet());

        return $productAbstractTransfer;
    }
}
