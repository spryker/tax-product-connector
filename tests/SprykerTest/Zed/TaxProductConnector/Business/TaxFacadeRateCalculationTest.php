<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\TaxProductConnector\Business;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\ItemTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Generated\Shared\Transfer\StoreTransfer;
use Orm\Zed\Country\Persistence\SpyCountryQuery;
use Orm\Zed\Product\Persistence\SpyProductAbstract;
use Orm\Zed\Tax\Persistence\SpyTaxRate;
use Orm\Zed\Tax\Persistence\SpyTaxSet;
use Orm\Zed\Tax\Persistence\SpyTaxSetTax;
use Spryker\Shared\Tax\TaxConstants;
use Spryker\Zed\Tax\Dependency\Facade\TaxToStoreFacadeInterface;
use Spryker\Zed\Tax\TaxDependencyProvider;
use Spryker\Zed\TaxProductConnector\Business\TaxProductConnectorFacade;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group TaxProductConnector
 * @group Business
 * @group Facade
 * @group TaxFacadeRateCalculationTest
 * Add your own group annotations below this line
 */
class TaxFacadeRateCalculationTest extends Unit
{
    /**
     * @var string
     */
    protected const DE_ISO_CODE = 'DE';

    /**
     * @var \SprykerTest\Zed\TaxProductConnector\TaxProductConnectorBusinessTester
     */
    protected $tester;

    /**
     * @return void
     */
    public function testSetTaxRateWhenExemptTaxRateUsedShouldSetZeroTaxRate(): void
    {
        $abstractProductEntity = $this->createAbstractProductWithTaxSet(20, 'GB');

        $quoteTransfer = new QuoteTransfer();

        $itemTransfer = new ItemTransfer();
        $itemTransfer->setIdProductAbstract($abstractProductEntity->getIdProductAbstract());
        $quoteTransfer->addItem($itemTransfer);

        $taxFacadeTest = $this->createTaxProductConnectorFacade();
        $taxFacadeTest->calculateProductItemTaxRate($quoteTransfer);

        $this->assertSame(0.0, $itemTransfer->getTaxRate());
    }

    /**
     * @return void
     */
    public function testSetTaxRateWhenExemptTaxRateUsedAndCountryMatchingShouldUseCountryRate(): void
    {
        //Arrange
        $this->tester->setDependency(TaxDependencyProvider::FACADE_STORE, $this->createTaxToStoreFacadeMock());
        $abstractProductEntity = $this->createAbstractProductWithTaxSet(20, static::DE_ISO_CODE);

        $quoteTransfer = new QuoteTransfer();

        $itemTransfer = new ItemTransfer();
        $itemTransfer->setIdProductAbstract($abstractProductEntity->getIdProductAbstract());
        $quoteTransfer->addItem($itemTransfer);

        //Act
        $taxFacadeTest = $this->createTaxProductConnectorFacade();
        $taxFacadeTest->calculateProductItemTaxRate($quoteTransfer);

        //Assert
        $this->assertSame(20.00, $itemTransfer->getTaxRate());
    }

    /**
     * @param int $taxRate
     * @param string $iso2Code
     *
     * @return \Orm\Zed\Product\Persistence\SpyProductAbstract
     */
    protected function createAbstractProductWithTaxSet(int $taxRate, string $iso2Code): SpyProductAbstract
    {
        $countryEntity = SpyCountryQuery::create()->findOneByIso2Code($iso2Code);

        $taxRateEntity1 = new SpyTaxRate();
        $taxRateEntity1->setRate($taxRate);
        $taxRateEntity1->setName('test rate');
        $taxRateEntity1->setFkCountry($countryEntity->getIdCountry());
        $taxRateEntity1->save();

        $taxRateEntity2 = new SpyTaxRate();
        $taxRateEntity2->setRate(13);
        $taxRateEntity2->setName('test rate');
        $taxRateEntity2->setFkCountry($countryEntity->getIdCountry());
        $taxRateEntity2->save();

        $taxRateExemptEntity = new SpyTaxRate();
        $taxRateExemptEntity->setRate(0);
        $taxRateExemptEntity->setName(TaxConstants::TAX_EXEMPT_PLACEHOLDER);
        $taxRateExemptEntity->save();

        $taxSetEntity = new SpyTaxSet();
        $taxSetEntity->setName('name of tax set');
        $taxSetEntity->save();

        $taxSetTaxRateEntity = new SpyTaxSetTax();
        $taxSetTaxRateEntity->setFkTaxSet($taxSetEntity->getIdTaxSet());
        $taxSetTaxRateEntity->setFkTaxRate($taxRateEntity1->getIdTaxRate());
        $taxSetTaxRateEntity->save();

        $taxSetTaxRateEntity = new SpyTaxSetTax();
        $taxSetTaxRateEntity->setFkTaxSet($taxSetEntity->getIdTaxSet());
        $taxSetTaxRateEntity->setFkTaxRate($taxRateEntity2->getIdTaxRate());
        $taxSetTaxRateEntity->save();

        $taxSetTaxRateEntity = new SpyTaxSetTax();
        $taxSetTaxRateEntity->setFkTaxSet($taxSetEntity->getIdTaxSet());
        $taxSetTaxRateEntity->setFkTaxRate($taxRateExemptEntity->getIdTaxRate());
        $taxSetTaxRateEntity->save();

        $abstractProductEntity = new SpyProductAbstract();
        $abstractProductEntity->setSku('test-abstract-sku');
        $abstractProductEntity->setAttributes('');
        $abstractProductEntity->setFkTaxSet($taxSetEntity->getIdTaxSet());
        $abstractProductEntity->save();

        return $abstractProductEntity;
    }

    /**
     * @return \Spryker\Zed\TaxProductConnector\Business\TaxProductConnectorFacade
     */
    protected function createTaxProductConnectorFacade(): TaxProductConnectorFacade
    {
        return new TaxProductConnectorFacade();
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\Spryker\Zed\Tax\Dependency\Facade\TaxToStoreFacadeInterface
     */
    protected function createTaxToStoreFacadeMock(): TaxToStoreFacadeInterface
    {
        $taxToStoreFacadeMock = $this->createMock(TaxToStoreFacadeInterface::class);
        $taxToStoreFacadeMock
            ->method('getCurrentStore')
            ->willReturn((new StoreTransfer())->setName(static::DE_ISO_CODE)->addCountry(static::DE_ISO_CODE));

        return $taxToStoreFacadeMock;
    }
}
