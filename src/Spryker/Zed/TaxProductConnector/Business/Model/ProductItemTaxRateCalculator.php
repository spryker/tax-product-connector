<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\TaxProductConnector\Business\Model;

use ArrayObject;
use Generated\Shared\Transfer\AddressTransfer;
use Generated\Shared\Transfer\CalculableObjectTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Generated\Shared\Transfer\StoreTransfer;
use Spryker\Zed\TaxProductConnector\Business\Calculator\CalculatorInterface;
use Spryker\Zed\TaxProductConnector\Dependency\Facade\TaxProductConnectorToStoreFacadeInterface;
use Spryker\Zed\TaxProductConnector\Dependency\Facade\TaxProductConnectorToTaxInterface;
use Spryker\Zed\TaxProductConnector\Persistence\TaxProductConnectorQueryContainer;
use Spryker\Zed\TaxProductConnector\Persistence\TaxProductConnectorQueryContainerInterface;

/**
 * @deprecated Use {@link \Spryker\Zed\TaxProductConnector\Business\Calculator\ProductItemTaxRateCalculator} instead.
 */
class ProductItemTaxRateCalculator implements CalculatorInterface
{
    /**
     * @var \Spryker\Zed\TaxProductConnector\Persistence\TaxProductConnectorQueryContainerInterface
     */
    protected $taxQueryContainer;

    /**
     * @var \Spryker\Zed\TaxProductConnector\Dependency\Facade\TaxProductConnectorToTaxInterface
     */
    protected $taxFacade;

    /**
     * @var string
     */
    protected $defaultTaxCountryIso2Code;

    /**
     * @var float
     */
    protected $defaultTaxRate;

    /**
     * @var \Spryker\Zed\TaxProductConnector\Dependency\Facade\TaxProductConnectorToStoreFacadeInterface
     */
    protected TaxProductConnectorToStoreFacadeInterface $storeFacade;

    /**
     * @param \Spryker\Zed\TaxProductConnector\Persistence\TaxProductConnectorQueryContainerInterface $taxQueryContainer
     * @param \Spryker\Zed\TaxProductConnector\Dependency\Facade\TaxProductConnectorToTaxInterface $taxFacade
     * @param \Spryker\Zed\TaxProductConnector\Dependency\Facade\TaxProductConnectorToStoreFacadeInterface $storeFacade
     */
    public function __construct(
        TaxProductConnectorQueryContainerInterface $taxQueryContainer,
        TaxProductConnectorToTaxInterface $taxFacade,
        TaxProductConnectorToStoreFacadeInterface $storeFacade
    ) {
        $this->taxQueryContainer = $taxQueryContainer;
        $this->taxFacade = $taxFacade;
        $this->storeFacade = $storeFacade;
    }

    /**
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     *
     * @return void
     */
    public function recalculate(QuoteTransfer $quoteTransfer)
    {
        $itemTransfers = $this->recalculateWithItemsAndShippingAddress($quoteTransfer->getItems(), $quoteTransfer->getShippingAddress(), $quoteTransfer->getStore());
        $quoteTransfer->setItems($itemTransfers);
    }

    /**
     * @param \Generated\Shared\Transfer\CalculableObjectTransfer $calculableObjectTransfer
     *
     * @return \Generated\Shared\Transfer\CalculableObjectTransfer
     */
    public function recalculateWithCalculableObject(CalculableObjectTransfer $calculableObjectTransfer): CalculableObjectTransfer
    {
        $itemTransfers = $this->recalculateWithItemsAndShippingAddress($calculableObjectTransfer->getItems(), $calculableObjectTransfer->getShippingAddress(), $calculableObjectTransfer->getStore());
        $calculableObjectTransfer->setItems($itemTransfers);

        return $calculableObjectTransfer;
    }

    /**
     * @param \ArrayObject<int, \Generated\Shared\Transfer\ItemTransfer> $itemTransfers
     * @param \Generated\Shared\Transfer\AddressTransfer|null $shippingAddressTransfer
     * @param \Generated\Shared\Transfer\StoreTransfer|null $storeTransfer
     *
     * @return \ArrayObject<int, \Generated\Shared\Transfer\ItemTransfer>
     */
    protected function recalculateWithItemsAndShippingAddress(
        ArrayObject $itemTransfers,
        ?AddressTransfer $shippingAddressTransfer,
        ?StoreTransfer $storeTransfer = null
    ): ArrayObject {
        $countryIso2Code = $this->getShippingCountryIso2Code($shippingAddressTransfer, $storeTransfer);
        $allIdProductAbstracts = $this->getAllIdAbstractProducts($itemTransfers);

        $taxRates = $this->findTaxRatesByAllIdProductAbstractsAndCountryIso2Code(
            $allIdProductAbstracts,
            $countryIso2Code,
        );

        return $this->setItemsTax($itemTransfers, $taxRates);
    }

    /**
     * @param \Generated\Shared\Transfer\AddressTransfer|null $shippingAddressTransfer
     * @param \Generated\Shared\Transfer\StoreTransfer|null $storeTransfer
     *
     * @return string
     */
    protected function getShippingCountryIso2Code(?AddressTransfer $shippingAddressTransfer, ?StoreTransfer $storeTransfer = null): string
    {
        if (!$shippingAddressTransfer || !$shippingAddressTransfer->getIso2Code()) {
            return $this->getDefaultTaxCountryIso2Code($storeTransfer);
        }

        return $shippingAddressTransfer->getIso2Code();
    }

    /**
     * @param \ArrayObject<int, \Generated\Shared\Transfer\ItemTransfer> $itemTransfers
     *
     * @return array<int>
     */
    protected function getAllIdAbstractProducts(ArrayObject $itemTransfers): array
    {
        $allIdProductAbstracts = [];
        foreach ($itemTransfers as $itemTransfer) {
            $allIdProductAbstracts[] = $itemTransfer->getIdProductAbstract();
        }

        return $allIdProductAbstracts;
    }

    /**
     * @param \ArrayObject<int, \Generated\Shared\Transfer\ItemTransfer> $itemTransfers
     * @param array $taxRates
     *
     * @return \ArrayObject<int, \Generated\Shared\Transfer\ItemTransfer>
     */
    protected function setItemsTax(ArrayObject $itemTransfers, array $taxRates): ArrayObject
    {
        foreach ($itemTransfers as $itemTransfer) {
            $itemTransfer->setTaxRate($this->getEffectiveTaxRate($taxRates, $itemTransfer->getIdProductAbstract()));
        }

        return $itemTransfers;
    }

    /**
     * @param array $taxRates
     * @param int $idProductAbstract
     *
     * @return float
     */
    protected function getEffectiveTaxRate(array $taxRates, $idProductAbstract)
    {
        foreach ($taxRates as $taxRate) {
            if ((int)$taxRate[TaxProductConnectorQueryContainer::COL_ID_ABSTRACT_PRODUCT] === (int)$idProductAbstract) {
                return (float)$taxRate[TaxProductConnectorQueryContainer::COL_MAX_TAX_RATE];
            }
        }

        return $this->getDefaultTaxRate();
    }

    /**
     * @param array $allIdProductAbstracts
     * @param string $countryIso2Code
     *
     * @return array
     */
    protected function findTaxRatesByAllIdProductAbstractsAndCountryIso2Code(
        array $allIdProductAbstracts,
        $countryIso2Code
    ): array {
        /** @var \Propel\Runtime\Collection\ArrayCollection $taxRatesCollection */
        $taxRatesCollection = $this->taxQueryContainer
            ->queryTaxSetByIdProductAbstractAndCountryIso2Code($allIdProductAbstracts, $countryIso2Code)
            ->find();

        return $taxRatesCollection->toArray();
    }

    /**
     * @param \Generated\Shared\Transfer\StoreTransfer|null $storeTransfer
     *
     * @return string
     */
    protected function getDefaultTaxCountryIso2Code(?StoreTransfer $storeTransfer = null): string
    {
        if ($this->defaultTaxCountryIso2Code === null) {
            if ($storeTransfer !== null) {
                $storeTransfer = $this->storeFacade->getStoreByName($storeTransfer->getName());
                $countries = $storeTransfer->getCountries();

                if ($countries) {
                    $this->defaultTaxCountryIso2Code = reset($countries);

                    return $this->defaultTaxCountryIso2Code;
                }
            }
            $this->defaultTaxCountryIso2Code = $this->taxFacade->getDefaultTaxCountryIso2Code();
        }

        return $this->defaultTaxCountryIso2Code;
    }

    /**
     * @return float
     */
    protected function getDefaultTaxRate(): float
    {
        if ($this->defaultTaxRate === null) {
            $this->defaultTaxRate = $this->taxFacade->getDefaultTaxRate();
        }

        return $this->defaultTaxRate;
    }
}
