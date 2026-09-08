<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\TaxProductConnector\Business;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\ProductAbstractTransfer;
use Orm\Zed\Product\Persistence\SpyProductAbstractQuery;
use Orm\Zed\Tax\Persistence\SpyTaxSetQuery;
use SprykerTest\Zed\TaxProductConnector\TaxProductConnectorBusinessTester;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group TaxProductConnector
 * @group Business
 * @group SaveTaxSetToProductAbstractByUuidTest
 *
 * Add your own group annotations below this line
 */
class SaveTaxSetToProductAbstractByUuidTest extends Unit
{
    protected const string UNKNOWN_UUID = '00000000-0000-0000-0000-000000000000';

    protected TaxProductConnectorBusinessTester $tester;

    public function testSaveTaxSetToProductAbstractResolvesTaxSetByUuid(): void
    {
        // Arrange
        $taxSetTransfer = $this->tester->haveTaxSet();
        $taxSetUuid = SpyTaxSetQuery::create()->findOneByIdTaxSet($taxSetTransfer->getIdTaxSet())->getUuid();
        $this->assertNotNull($taxSetUuid);

        $existingProductAbstractTransfer = $this->tester->haveProductAbstract();
        $productAbstractTransfer = (new ProductAbstractTransfer())
            ->setIdProductAbstract($existingProductAbstractTransfer->getIdProductAbstract())
            ->setTaxSetUuid($taxSetUuid);

        // Act
        $this->tester->getFacade()->saveTaxSetToProductAbstract($productAbstractTransfer);

        // Assert
        $productAbstractEntity = SpyProductAbstractQuery::create()
            ->findOneByIdProductAbstract($existingProductAbstractTransfer->getIdProductAbstract());
        $this->assertSame($taxSetTransfer->getIdTaxSet(), $productAbstractEntity->getFkTaxSet());
    }

    public function testSaveTaxSetToProductAbstractKeepsAssignedTaxSetWhenUuidIsUnknown(): void
    {
        // Arrange
        $taxSetTransfer = $this->tester->haveTaxSet();
        $existingProductAbstractTransfer = $this->tester->haveProductAbstract();

        $this->tester->getFacade()->saveTaxSetToProductAbstract(
            (new ProductAbstractTransfer())
                ->setIdProductAbstract($existingProductAbstractTransfer->getIdProductAbstract())
                ->setIdTaxSet($taxSetTransfer->getIdTaxSet()),
        );

        $productAbstractTransfer = (new ProductAbstractTransfer())
            ->setIdProductAbstract($existingProductAbstractTransfer->getIdProductAbstract())
            ->setTaxSetUuid(static::UNKNOWN_UUID);

        // Act
        $this->tester->getFacade()->saveTaxSetToProductAbstract($productAbstractTransfer);

        // Assert
        $productAbstractEntity = SpyProductAbstractQuery::create()
            ->findOneByIdProductAbstract($existingProductAbstractTransfer->getIdProductAbstract());
        $this->assertSame(
            $taxSetTransfer->getIdTaxSet(),
            $productAbstractEntity->getFkTaxSet(),
            'An unresolvable tax set UUID must not unassign the currently assigned tax set.',
        );
    }

    public function testSaveTaxSetToProductAbstractPrefersIdTaxSetOverUuid(): void
    {
        // Arrange
        $taxSetTransfer = $this->tester->haveTaxSet();
        $otherTaxSetTransfer = $this->tester->haveTaxSet();
        $otherTaxSetUuid = SpyTaxSetQuery::create()->findOneByIdTaxSet($otherTaxSetTransfer->getIdTaxSet())->getUuid();

        $existingProductAbstractTransfer = $this->tester->haveProductAbstract();
        $productAbstractTransfer = (new ProductAbstractTransfer())
            ->setIdProductAbstract($existingProductAbstractTransfer->getIdProductAbstract())
            ->setIdTaxSet($taxSetTransfer->getIdTaxSet())
            ->setTaxSetUuid($otherTaxSetUuid);

        // Act
        $this->tester->getFacade()->saveTaxSetToProductAbstract($productAbstractTransfer);

        // Assert
        $productAbstractEntity = SpyProductAbstractQuery::create()
            ->findOneByIdProductAbstract($existingProductAbstractTransfer->getIdProductAbstract());
        $this->assertSame($taxSetTransfer->getIdTaxSet(), $productAbstractEntity->getFkTaxSet());
    }
}
