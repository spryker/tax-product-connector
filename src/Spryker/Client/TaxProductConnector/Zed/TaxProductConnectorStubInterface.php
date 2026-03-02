<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\TaxProductConnector\Zed;

use Generated\Shared\Transfer\ProductAbstractTransfer;
use Generated\Shared\Transfer\TaxSetResponseTransfer;

interface TaxProductConnectorStubInterface
{
    public function getTaxSetForProductAbstract(ProductAbstractTransfer $productAbstractTransfer): TaxSetResponseTransfer;
}
