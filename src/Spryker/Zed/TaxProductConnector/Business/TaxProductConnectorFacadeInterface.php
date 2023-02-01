<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\TaxProductConnector\Business;

use Generated\Shared\Transfer\CalculableObjectTransfer;
use Generated\Shared\Transfer\ProductAbstractCollectionTransfer;
use Generated\Shared\Transfer\ProductAbstractCriteriaTransfer;
use Generated\Shared\Transfer\ProductAbstractTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Generated\Shared\Transfer\TaxSetResponseTransfer;

interface TaxProductConnectorFacadeInterface
{
    /**
     * Specification:
     * - Save tax set id to product abstract table
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\ProductAbstractTransfer $productConcreteTransfer
     *
     * @return \Generated\Shared\Transfer\ProductAbstractTransfer
     */
    public function saveTaxSetToProductAbstract(ProductAbstractTransfer $productConcreteTransfer);

    /**
     * Specification:
     * - Read tax set from database and sets PriceProductTransfer on ProductAbstractTransfer
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\ProductAbstractTransfer $productAbstractTransfer
     *
     * @return \Generated\Shared\Transfer\ProductAbstractTransfer
     */
    public function mapTaxSet(ProductAbstractTransfer $productAbstractTransfer);

    /**
     * Specification:
     * - Finds tax set in database by ProductAbstractTransfer.idProductAbstract.
     * - Sets ProductAbstractTransfer.idTaxSet transfer property.
     * - Requires `ProductAbstractTransfer.idProductAbstract` to be provided.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\ProductAbstractTransfer $productAbstractTransfer
     *
     * @return \Generated\Shared\Transfer\ProductAbstractTransfer
     */
    public function expandProductAbstract(ProductAbstractTransfer $productAbstractTransfer): ProductAbstractTransfer;

    /**
     * Specification:
     *  - Set tax rate for each item based on quote level (BC) or item level shipments.
     *  - Executes the stack of {@link \Spryker\Zed\TaxProductConnectorExtension\Communication\Dependency\Plugin\ShippingAddressValidatorPluginInterface} plugins.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     *
     * @return void
     */
    public function calculateProductItemTaxRate(QuoteTransfer $quoteTransfer);

    /**
     * Specification:
     *  - Set tax rate for each item based on quote level (BC) or item level shipments.
     *  - Executes the stack of {@link \Spryker\Zed\TaxProductConnectorExtension\Communication\Dependency\Plugin\ShippingAddressValidatorPluginInterface} plugins.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\CalculableObjectTransfer $calculableObjectTransfer
     *
     * @return \Generated\Shared\Transfer\CalculableObjectTransfer
     */
    public function calculateProductItemTaxRateForCalculableObjectTransfer(CalculableObjectTransfer $calculableObjectTransfer): CalculableObjectTransfer;

    /**
     * Specification:
     *  - Returns response with tax set for abstract product.
     *  - If tax set is null - sets error message and isSuccess to false.
     *
     * @api
     *
     * @deprecated Will be removed without replacement.
     *
     * @param \Generated\Shared\Transfer\ProductAbstractTransfer $productAbstractTransfer
     *
     * @return \Generated\Shared\Transfer\TaxSetResponseTransfer
     */
    public function getTaxSetForProductAbstract(ProductAbstractTransfer $productAbstractTransfer): TaxSetResponseTransfer;

    /**
     * Specification:
     * - Expands each product abstract with a corresponding tax set.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\ProductAbstractCollectionTransfer $productAbstractCollectionTransfer
     * @param \Generated\Shared\Transfer\ProductAbstractCriteriaTransfer $productAbstractCriteriaTransfer
     *
     * @return \Generated\Shared\Transfer\ProductAbstractCollectionTransfer
     */
    public function expandProductAbstractCollectionWithTaxSets(
        ProductAbstractCollectionTransfer $productAbstractCollectionTransfer,
        ProductAbstractCriteriaTransfer $productAbstractCriteriaTransfer
    ): ProductAbstractCollectionTransfer;
}
