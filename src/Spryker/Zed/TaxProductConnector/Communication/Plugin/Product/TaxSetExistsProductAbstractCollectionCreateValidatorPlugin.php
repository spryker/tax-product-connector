<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Zed\TaxProductConnector\Communication\Plugin\Product;

use Generated\Shared\Transfer\ProductAbstractCollectionRequestTransfer;
use Generated\Shared\Transfer\ProductAbstractCollectionResponseTransfer;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;
use Spryker\Zed\ProductExtension\Dependency\Plugin\ProductAbstractCollectionCreateValidatorPluginInterface;

/**
 * @method \Spryker\Zed\TaxProductConnector\Business\TaxProductConnectorBusinessFactory getBusinessFactory()
 */
class TaxSetExistsProductAbstractCollectionCreateValidatorPlugin extends AbstractPlugin implements ProductAbstractCollectionCreateValidatorPluginInterface
{
    /**
     * {@inheritDoc}
     * - Validates the tax set UUID referenced in `ProductAbstractTransfer.taxSetUuid`.
     * - Resolves all referenced UUIDs of the collection with a single query.
     * - Adds an error per abstract product referencing a tax set UUID that does not exist.
     * - Returns the response unchanged when no abstract product references a tax set.
     *
     * @api
     */
    public function validate(
        ProductAbstractCollectionRequestTransfer $productAbstractCollectionRequestTransfer,
        ProductAbstractCollectionResponseTransfer $productAbstractCollectionResponseTransfer
    ): ProductAbstractCollectionResponseTransfer {
        return $this->getBusinessFactory()
            ->createProductTaxSetValidator()
            ->validateProductAbstractCollection($productAbstractCollectionRequestTransfer, $productAbstractCollectionResponseTransfer);
    }
}
