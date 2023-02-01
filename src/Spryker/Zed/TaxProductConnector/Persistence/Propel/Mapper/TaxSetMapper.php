<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\TaxProductConnector\Persistence\Propel\Mapper;

use Generated\Shared\Transfer\CountryTransfer;
use Generated\Shared\Transfer\TaxRateTransfer;
use Generated\Shared\Transfer\TaxSetTransfer;
use Orm\Zed\Tax\Persistence\SpyTaxSet;
use Propel\Runtime\Collection\ObjectCollection;

class TaxSetMapper implements TaxSetMapperInterface
{
    /**
     * @param \Orm\Zed\Tax\Persistence\SpyTaxSet $taxSetEntity
     *
     * @return \Generated\Shared\Transfer\TaxSetTransfer
     */
    public function mapTaxSetEntityToTaxSetTransfer(SpyTaxSet $taxSetEntity): TaxSetTransfer
    {
        $taxSetTransfer = new TaxSetTransfer();
        $taxSetTransfer->fromArray($taxSetEntity->toArray(), true);
        foreach ($taxSetEntity->getSpyTaxRates() as $taxRate) {
            $taxRateTransfer = (new TaxRateTransfer())->fromArray($taxRate->toArray(), true);
            /** @var \Orm\Zed\Country\Persistence\SpyCountry|null $countryEntity */
            $countryEntity = $taxRate->getCountry();
            if ($countryEntity) {
                $countryTransfer = (new CountryTransfer())->fromArray(
                    $countryEntity->toArray(),
                    true,
                );
                $taxRateTransfer->setCountry($countryTransfer);
            }
            $taxSetTransfer->addTaxRate($taxRateTransfer);
        }

        return $taxSetTransfer;
    }

    /**
     * @param \Propel\Runtime\Collection\ObjectCollection<\Orm\Zed\Product\Persistence\SpyProductAbstract> $productAbstractEntities
     * @param array<int, \Generated\Shared\Transfer\TaxSetTransfer> $taxSetTransfers
     *
     * @return array<int, \Generated\Shared\Transfer\TaxSetTransfer>
     */
    public function mapProductAbstractEntitiesToTaxSetTransfers(ObjectCollection $productAbstractEntities, array $taxSetTransfers = []): array
    {
        if (!$productAbstractEntities->count()) {
            return [];
        }

        foreach ($productAbstractEntities as $productAbstractEntity) {
            if ($productAbstractEntity->getSpyTaxSet()) {
                $taxSetTransfers[$productAbstractEntity->getIdProductAbstract()] = (new TaxSetTransfer())
                    ->fromArray($productAbstractEntity->getSpyTaxSet()->toArray(), true);
            }
        }

        return $taxSetTransfers;
    }
}
