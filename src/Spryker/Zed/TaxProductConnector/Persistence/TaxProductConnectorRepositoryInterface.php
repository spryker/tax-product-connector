<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\TaxProductConnector\Persistence;

use Generated\Shared\Transfer\TaxSetTransfer;

interface TaxProductConnectorRepositoryInterface
{
    public function findTaxSetByProductAbstractSku(string $productAbstractSku): ?TaxSetTransfer;

    public function findByIdProductAbstract(int $idProductAbstract): ?TaxSetTransfer;

    /**
     * @param array<int> $productAbstractIds
     *
     * @return array<int, \Generated\Shared\Transfer\TaxSetTransfer>
     */
    public function getTaxSets(array $productAbstractIds): array;

    public function isTaxSetUuidSupported(): bool;

    public function findIdTaxSetByUuid(string $uuid): ?int;

    /**
     * @param array<string> $uuids
     *
     * @return array<string>
     */
    public function getKnownTaxSetUuids(array $uuids): array;
}
