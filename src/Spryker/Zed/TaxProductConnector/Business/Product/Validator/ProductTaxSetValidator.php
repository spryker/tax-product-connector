<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Zed\TaxProductConnector\Business\Product\Validator;

use Generated\Shared\Transfer\ErrorTransfer;
use Generated\Shared\Transfer\ProductAbstractCollectionRequestTransfer;
use Generated\Shared\Transfer\ProductAbstractCollectionResponseTransfer;
use Spryker\Zed\TaxProductConnector\Persistence\TaxProductConnectorRepositoryInterface;

class ProductTaxSetValidator implements ProductTaxSetValidatorInterface
{
    protected const string ERROR_MESSAGE_TAX_SET_DOES_NOT_EXIST = 'Tax set with UUID "%s" does not exist.';

    public function __construct(
        protected readonly TaxProductConnectorRepositoryInterface $taxProductConnectorRepository,
    ) {
    }

    public function validateProductAbstractCollection(
        ProductAbstractCollectionRequestTransfer $productAbstractCollectionRequestTransfer,
        ProductAbstractCollectionResponseTransfer $productAbstractCollectionResponseTransfer
    ): ProductAbstractCollectionResponseTransfer {
        if (!$this->taxProductConnectorRepository->isTaxSetUuidSupported()) {
            return $productAbstractCollectionResponseTransfer;
        }

        $uuidsBySku = [];

        foreach ($productAbstractCollectionRequestTransfer->getProductAbstracts() as $productAbstractTransfer) {
            $uuid = $productAbstractTransfer->getTaxSetUuid();

            if ($uuid !== null && $uuid !== '') {
                $uuidsBySku[(string)$productAbstractTransfer->getSku()] = $uuid;
            }
        }

        if ($uuidsBySku === []) {
            return $productAbstractCollectionResponseTransfer;
        }

        // Resolved for the whole collection at once so the repository executes a single query.
        $knownUuids = $this->taxProductConnectorRepository->getKnownTaxSetUuids(
            array_values(array_unique($uuidsBySku)),
        );

        foreach ($uuidsBySku as $sku => $uuid) {
            if (!in_array($uuid, $knownUuids, true)) {
                $productAbstractCollectionResponseTransfer->addError($this->createError((string)$sku, $uuid));
            }
        }

        return $productAbstractCollectionResponseTransfer;
    }

    protected function createError(?string $entityIdentifier, string $unknownUuid): ErrorTransfer
    {
        return (new ErrorTransfer())
            ->setEntityIdentifier($entityIdentifier)
            ->setMessage(sprintf(static::ERROR_MESSAGE_TAX_SET_DOES_NOT_EXIST, $unknownUuid));
    }
}
