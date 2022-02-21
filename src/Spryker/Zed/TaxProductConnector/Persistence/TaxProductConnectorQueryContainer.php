<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\TaxProductConnector\Persistence;

use Orm\Zed\Country\Persistence\Map\SpyCountryTableMap;
use Orm\Zed\Product\Persistence\Map\SpyProductAbstractTableMap;
use Orm\Zed\Tax\Persistence\Map\SpyTaxRateTableMap;
use Orm\Zed\Tax\Persistence\Map\SpyTaxSetTableMap;
use Orm\Zed\Tax\Persistence\SpyTaxSetQuery;
use Propel\Runtime\ActiveQuery\Criteria;
use Spryker\Shared\Tax\TaxConstants;
use Spryker\Zed\Kernel\Persistence\AbstractQueryContainer;

/**
 * @method \Spryker\Zed\TaxProductConnector\Persistence\TaxProductConnectorPersistenceFactory getFactory()
 */
class TaxProductConnectorQueryContainer extends AbstractQueryContainer implements TaxProductConnectorQueryContainerInterface
{
    /**
     * @var string
     */
    public const COL_MAX_TAX_RATE = 'MaxTaxRate';

    /**
     * @var string
     */
    public const COL_ID_ABSTRACT_PRODUCT = 'IdProductAbstract';

    /**
     * @var string
     */
    public const COL_COUNTRY_CODE = 'COUNTRY_CODE';

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @todo CD-427 Follow naming conventions and use method name starting with 'query*'
     *
     * @param int $idTaxRate
     *
     * @return \Orm\Zed\Product\Persistence\SpyProductAbstractQuery
     */
    public function getAbstractAbstractIdsForTaxRate($idTaxRate)
    {
        /** @phpstan-var \Orm\Zed\Product\Persistence\SpyProductAbstractQuery */
        return $this->getFactory()->createProductAbstractQuery()
            ->select([
                SpyProductAbstractTableMap::COL_ID_PRODUCT_ABSTRACT,
            ])
            ->useSpyTaxSetQuery()
                ->useSpyTaxSetTaxQuery()
                    ->useSpyTaxRateQuery()
                    ->filterByIdTaxRate($idTaxRate)
                    ->endUse()
                ->endUse()
            ->endUse();
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @todo CD-427 Follow naming conventions and use method name starting with 'query*'
     *
     * @param int $idTaxSet
     *
     * @return \Orm\Zed\Product\Persistence\SpyProductAbstractQuery
     */
    public function getProductAbstractIdsForTaxSet($idTaxSet)
    {
        return $this->getFactory()->createProductAbstractQuery()
            ->addJoin(
                SpyProductAbstractTableMap::COL_FK_TAX_SET,
                SpyTaxSetTableMap::COL_ID_TAX_SET,
                Criteria::INNER_JOIN,
            )
            ->filterByFkTaxSet($idTaxSet)
            ->select([
                SpyProductAbstractTableMap::COL_ID_PRODUCT_ABSTRACT,
            ]);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param int $idProductAbstract
     *
     * @return \Orm\Zed\Product\Persistence\SpyProductAbstractQuery
     */
    public function queryProductAbstractById($idProductAbstract)
    {
        return $this->getFactory()
            ->createProductAbstractQuery()
            ->filterByIdProductAbstract($idProductAbstract);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param int $idProductAbstract
     *
     * @return \Orm\Zed\Tax\Persistence\SpyTaxSetQuery
     */
    public function queryTaxSetForProductAbstract($idProductAbstract)
    {
        /** @phpstan-var \Orm\Zed\Tax\Persistence\SpyTaxSetQuery */
        return $this->getFactory()
            ->createTaxSetQuery()
            ->useSpyProductAbstractQuery()
                ->filterByIdProductAbstract($idProductAbstract)
            ->endUse();
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @module Country
     *
     * @deprecated Use {@link queryTaxSetByIdProductAbstractAndCountryIso2Codes()} instead.
     *
     * @param array<int> $productAbstractIds
     * @param string $countryIso2Code
     *
     * @return \Orm\Zed\Tax\Persistence\SpyTaxSetQuery
     */
    public function queryTaxSetByIdProductAbstractAndCountryIso2Code(array $productAbstractIds, $countryIso2Code)
    {
        /** @phpstan-var \Orm\Zed\Tax\Persistence\SpyTaxSetQuery */
        return $this->getFactory()->createTaxSetQuery()
            ->useSpyProductAbstractQuery()
                ->filterByIdProductAbstract($productAbstractIds, Criteria::IN)
                ->withColumn(SpyProductAbstractTableMap::COL_ID_PRODUCT_ABSTRACT, static::COL_ID_ABSTRACT_PRODUCT)
                ->groupBy(SpyProductAbstractTableMap::COL_ID_PRODUCT_ABSTRACT)
            ->endUse()
            ->useSpyTaxSetTaxQuery()
                ->useSpyTaxRateQuery()
                    ->useCountryQuery()
                        ->filterByIso2Code($countryIso2Code)
                    ->endUse()
                    ->_or()
                    ->filterByName(TaxConstants::TAX_EXEMPT_PLACEHOLDER)
                ->endUse()
                ->withColumn('MAX(' . SpyTaxRateTableMap::COL_RATE . ')', static::COL_MAX_TAX_RATE)
            ->endUse()
            ->select([static::COL_MAX_TAX_RATE]);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @module Country
     *
     * @param array<int> $productAbstractIds
     * @param array<string> $countryIso2Codes
     *
     * @return \Orm\Zed\Tax\Persistence\SpyTaxSetQuery
     */
    public function queryTaxSetByIdProductAbstractAndCountryIso2Codes(array $productAbstractIds, array $countryIso2Codes): SpyTaxSetQuery
    {
        /** @phpstan-var \Orm\Zed\Tax\Persistence\SpyTaxSetQuery */
        return $this->getFactory()
            ->createTaxSetQuery()
            ->useSpyProductAbstractQuery()
                ->filterByIdProductAbstract($productAbstractIds, Criteria::IN)
                ->withColumn(SpyProductAbstractTableMap::COL_ID_PRODUCT_ABSTRACT, static::COL_ID_ABSTRACT_PRODUCT)
                ->groupBy(SpyProductAbstractTableMap::COL_ID_PRODUCT_ABSTRACT)
            ->endUse()
            ->useSpyTaxSetTaxQuery()
                ->useSpyTaxRateQuery()
                    ->useCountryQuery()
                        ->filterByIso2Code($countryIso2Codes, Criteria::IN)
                        ->withColumn(SpyCountryTableMap::COL_ISO2_CODE, static::COL_COUNTRY_CODE)
                        ->groupBy(SpyCountryTableMap::COL_ISO2_CODE)
                    ->endUse()
                    ->_or()
                    ->filterByFkCountry(null)
                ->endUse()
                ->withColumn('MAX(' . SpyTaxRateTableMap::COL_RATE . ')', static::COL_MAX_TAX_RATE)
            ->endUse()
            ->select([static::COL_COUNTRY_CODE, static::COL_MAX_TAX_RATE, SpyProductAbstractTableMap::COL_ID_PRODUCT_ABSTRACT]);
    }
}
