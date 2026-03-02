<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\TaxProductConnector\Dependency\Facade;

interface TaxProductConnectorToTaxInterface
{
    public function getDefaultTaxCountryIso2Code(): string;

    public function getDefaultTaxRate(): float;
}
