<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\TaxProductConnector\Business\Product;

use Generated\Shared\Transfer\ProductAbstractTransfer;
use Orm\Zed\Product\Persistence\SpyProductAbstract;
use Spryker\Zed\TaxProductConnector\Business\Exception\ProductAbstractNotFoundException;
use Spryker\Zed\TaxProductConnector\Persistence\TaxProductConnectorQueryContainerInterface;
use Spryker\Zed\TaxProductConnector\Persistence\TaxProductConnectorRepositoryInterface;

class ProductAbstractTaxWriter
{
    /**
     * @var \Spryker\Zed\TaxProductConnector\Persistence\TaxProductConnectorQueryContainerInterface
     */
    protected $queryContainer;

    public function __construct(
        TaxProductConnectorQueryContainerInterface $queryContainer,
        protected readonly TaxProductConnectorRepositoryInterface $taxProductConnectorRepository
    ) {
        $this->queryContainer = $queryContainer;
    }

    /**
     * @param \Generated\Shared\Transfer\ProductAbstractTransfer $productConcreteTransfer
     *
     * @throws \Spryker\Zed\TaxProductConnector\Business\Exception\ProductAbstractNotFoundException
     *
     * @return \Generated\Shared\Transfer\ProductAbstractTransfer
     */
    public function saveTaxSetToProductAbstract(ProductAbstractTransfer $productConcreteTransfer)
    {
        $productConcreteTransfer->requireIdProductAbstract();

        $productAbstractEntity = $this->queryContainer
            ->queryProductAbstractById($productConcreteTransfer->getIdProductAbstract())
            ->findOne();

        if ($productAbstractEntity === null) {
            throw new ProductAbstractNotFoundException(
                sprintf(
                    'Could not assign tax set, product abstract with id "%d" not found.',
                    $productConcreteTransfer->getIdProductAbstract(),
                ),
            );
        }

        $productAbstractEntity->setFkTaxSet($this->resolveIdTaxSet($productConcreteTransfer, $productAbstractEntity));
        $productAbstractEntity->save();

        return $productConcreteTransfer;
    }

    protected function resolveIdTaxSet(
        ProductAbstractTransfer $productAbstractTransfer,
        SpyProductAbstract $productAbstractEntity
    ): ?int {
        if ($productAbstractTransfer->getIdTaxSet() !== null) {
            return $productAbstractTransfer->getIdTaxSet();
        }

        $taxSetUuid = $productAbstractTransfer->getTaxSetUuid();

        if ($taxSetUuid === null) {
            return null;
        }

        // A requested UUID that cannot be resolved must not unassign the currently assigned tax set.
        return $this->taxProductConnectorRepository->findIdTaxSetByUuid($taxSetUuid)
            ?? $productAbstractEntity->getFkTaxSet();
    }
}
