<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\TaxProductConnector\Persistence;

use Generated\Shared\Transfer\TaxSetTransfer;
use Orm\Zed\Tax\Persistence\Map\SpyTaxSetTableMap;
use Orm\Zed\Tax\Persistence\SpyTaxSet;
use Propel\Runtime\Collection\ObjectCollection;
use Spryker\Zed\Kernel\Persistence\AbstractRepository;

/**
 * @method \Spryker\Zed\TaxProductConnector\Persistence\TaxProductConnectorPersistenceFactory getFactory()
 */
class TaxProductConnectorRepository extends AbstractRepository implements TaxProductConnectorRepositoryInterface
{
    /**
     * @uses \Orm\Zed\Tax\Persistence\Map\SpyTaxSetTableMap::COL_UUID
     */
    protected const string COLUMN_UUID = 'uuid';

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
            ->mapTaxSetEntityToTaxSetTransfer($taxSet, $this->getTaxRateEntitiesWithCountry($taxSet));
    }

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
            ->mapTaxSetEntityToTaxSetTransfer($taxSet, $this->getTaxRateEntitiesWithCountry($taxSet));
    }

    /**
     * @module Tax
     * @module Country
     *
     * @param \Orm\Zed\Tax\Persistence\SpyTaxSet $taxSetEntity
     *
     * @return \Propel\Runtime\Collection\ObjectCollection<\Orm\Zed\Tax\Persistence\SpyTaxRate>
     */
    protected function getTaxRateEntitiesWithCountry(SpyTaxSet $taxSetEntity): ObjectCollection
    {
        /** @var \Propel\Runtime\Collection\ObjectCollection<\Orm\Zed\Tax\Persistence\SpyTaxRate> $taxRateEntities */
        $taxRateEntities = $this->getFactory()
            ->createTaxRateQuery()
            ->leftJoinWithCountry()
            ->useSpyTaxSetTaxQuery()
                ->filterByFkTaxSet($taxSetEntity->getIdTaxSet())
            ->endUse()
            ->find();

        return $taxRateEntities;
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

    public function isTaxSetUuidSupported(): bool
    {
        return SpyTaxSetTableMap::getTableMap()->hasColumn(static::COLUMN_UUID);
    }

    /**
     * @module Tax
     */
    public function findIdTaxSetByUuid(string $uuid): ?int
    {
        if (!$this->isTaxSetUuidSupported()) {
            return null;
        }

        $taxSetEntity = $this->getFactory()
            ->createTaxSetQuery()
            ->filterByUuid($uuid)
            ->findOne();

        return $taxSetEntity?->getIdTaxSet();
    }

    /**
     * @module Tax
     *
     * @param array<string> $uuids
     *
     * @return array<string>
     */
    public function getKnownTaxSetUuids(array $uuids): array
    {
        if ($uuids === [] || !$this->isTaxSetUuidSupported()) {
            return [];
        }

        return $this->getFactory()
            ->createTaxSetQuery()
            ->filterByUuid_In($uuids)
            ->select([SpyTaxSetTableMap::COL_UUID])
            ->find()
            ->getData();
    }
}
