<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\TaxProductConnector\Persistence;

use Generated\Shared\Transfer\TaxSetTransfer;
use Spryker\Zed\Kernel\Persistence\AbstractRepository;

/**
 * @method \Spryker\Zed\TaxProductConnector\Persistence\TaxProductConnectorPersistenceFactory getFactory()
 */
class TaxProductConnectorRepository extends AbstractRepository implements TaxProductConnectorRepositoryInterface
{
    /**
     * @param string $productAbstractSku
     *
     * @return \Generated\Shared\Transfer\TaxSetTransfer|null
     */
    public function findTaxSetByProductAbstractSku(string $productAbstractSku): ?TaxSetTransfer
    {
        /** @var \Orm\Zed\Tax\Persistence\SpyTaxSet|null $taxSet */
        $taxSet = $this->getFactory()->createTaxSetQuery()
            ->useSpyProductAbstractQuery()
                ->filterBySku($productAbstractSku)
            ->endUse()
            ->findOne();

        if (!$taxSet) {
            return null;
        }

        return $this->getFactory()
            ->createTaxSetMapper()
            ->mapTaxSetEntityToTaxSetTransfer($taxSet);
    }

    /**
     * @param int $idProductAbstract
     *
     * @return \Generated\Shared\Transfer\TaxSetTransfer|null
     */
    public function findByIdProductAbstract(int $idProductAbstract): ?TaxSetTransfer
    {
        /** @var \Orm\Zed\Tax\Persistence\SpyTaxSet|null $taxSet */
        $taxSet = $this->getFactory()->createTaxSetQuery()
            ->useSpyProductAbstractQuery()
                ->filterByIdProductAbstract($idProductAbstract)
            ->endUse()
            ->findOne();

        if (!$taxSet) {
            return null;
        }

        return $this->getFactory()
            ->createTaxSetMapper()
            ->mapTaxSetEntityToTaxSetTransfer($taxSet);
    }

    /**
     * @param array<int> $productAbstractIds
     *
     * @return array<int, \Generated\Shared\Transfer\TaxSetTransfer>
     */
    public function getTaxSets(array $productAbstractIds): array
    {
        $productAbstractEntities = $this->getFactory()
            ->createProductAbstractQuery()
            ->filterByIdProductAbstract_In($productAbstractIds)
            ->leftJoinWithSpyTaxSet()
            ->find();

        return $this->getFactory()
            ->createTaxSetMapper()
            ->mapProductAbstractEntitiesToTaxSetTransfers($productAbstractEntities);
    }
}
