<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\TaxProductConnector\Business\Product\Validator;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\ProductAbstractCollectionRequestTransfer;
use Generated\Shared\Transfer\ProductAbstractCollectionResponseTransfer;
use Generated\Shared\Transfer\ProductAbstractTransfer;
use Spryker\Zed\TaxProductConnector\Business\Product\Validator\ProductTaxSetValidator;
use Spryker\Zed\TaxProductConnector\Persistence\TaxProductConnectorRepositoryInterface;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group TaxProductConnector
 * @group Business
 * @group Product
 * @group Validator
 * @group ProductTaxSetValidatorTest
 *
 * Add your own group annotations below this line
 */
class ProductTaxSetValidatorTest extends Unit
{
    protected const string KNOWN_UUID = '11111111-1111-1111-1111-111111111111';

    protected const string UNKNOWN_UUID = '00000000-0000-0000-0000-000000000000';

    protected const string PRODUCT_ABSTRACT_SKU = 'abstract-sku';

    protected const string OTHER_PRODUCT_ABSTRACT_SKU = 'other-abstract-sku';

    protected const string ERROR_MESSAGE_PATTERN = 'Tax set with UUID "%s" does not exist.';

    public function testValidateSkipsValidationWhenTaxSetUuidIsNotSupported(): void
    {
        // Arrange
        $repositoryMock = $this->createRepositoryMock(false, []);
        $repositoryMock->expects($this->never())->method('getKnownTaxSetUuids');

        $request = $this->createRequest([static::PRODUCT_ABSTRACT_SKU => static::UNKNOWN_UUID]);

        // Act
        $response = (new ProductTaxSetValidator($repositoryMock))
            ->validateProductAbstractCollection($request, new ProductAbstractCollectionResponseTransfer());

        // Assert
        $this->assertCount(0, $response->getErrors());
    }

    public function testValidateReturnsNoErrorsWhenTaxSetUuidIsKnown(): void
    {
        // Arrange
        $repositoryMock = $this->createRepositoryMock(true, [static::KNOWN_UUID]);
        $request = $this->createRequest([static::PRODUCT_ABSTRACT_SKU => static::KNOWN_UUID]);

        // Act
        $response = (new ProductTaxSetValidator($repositoryMock))
            ->validateProductAbstractCollection($request, new ProductAbstractCollectionResponseTransfer());

        // Assert
        $this->assertCount(0, $response->getErrors());
    }

    public function testValidateReturnsErrorWithMessageForUnknownTaxSetUuid(): void
    {
        // Arrange
        $repositoryMock = $this->createRepositoryMock(true, []);
        $request = $this->createRequest([static::PRODUCT_ABSTRACT_SKU => static::UNKNOWN_UUID]);

        // Act
        $response = (new ProductTaxSetValidator($repositoryMock))
            ->validateProductAbstractCollection($request, new ProductAbstractCollectionResponseTransfer());

        // Assert
        $this->assertCount(1, $response->getErrors());
        $errorTransfer = $response->getErrors()->offsetGet(0);
        $this->assertSame(static::PRODUCT_ABSTRACT_SKU, $errorTransfer->getEntityIdentifier());
        $this->assertSame(sprintf(static::ERROR_MESSAGE_PATTERN, static::UNKNOWN_UUID), $errorTransfer->getMessage());
    }

    public function testValidateReturnsErrorsOnlyForUnknownTaxSetUuids(): void
    {
        // Arrange
        $repositoryMock = $this->createRepositoryMock(true, [static::KNOWN_UUID]);
        $request = $this->createRequest([
            static::PRODUCT_ABSTRACT_SKU => static::KNOWN_UUID,
            static::OTHER_PRODUCT_ABSTRACT_SKU => static::UNKNOWN_UUID,
        ]);

        // Act
        $response = (new ProductTaxSetValidator($repositoryMock))
            ->validateProductAbstractCollection($request, new ProductAbstractCollectionResponseTransfer());

        // Assert
        $this->assertCount(1, $response->getErrors());
        $this->assertSame(static::OTHER_PRODUCT_ABSTRACT_SKU, $response->getErrors()->offsetGet(0)->getEntityIdentifier());
    }

    public function testValidateReturnsErrorPerProductAbstractForSameUnknownTaxSetUuid(): void
    {
        // Arrange
        $repositoryMock = $this->createRepositoryMock(true, []);
        $request = $this->createRequest([
            static::PRODUCT_ABSTRACT_SKU => static::UNKNOWN_UUID,
            static::OTHER_PRODUCT_ABSTRACT_SKU => static::UNKNOWN_UUID,
        ]);

        // Act
        $response = (new ProductTaxSetValidator($repositoryMock))
            ->validateProductAbstractCollection($request, new ProductAbstractCollectionResponseTransfer());

        // Assert
        $this->assertCount(2, $response->getErrors());
    }

    public function testValidateResolvesUniqueUuidsOnceForTheWholeCollection(): void
    {
        // Arrange
        $repositoryMock = $this->createMock(TaxProductConnectorRepositoryInterface::class);
        $repositoryMock->method('isTaxSetUuidSupported')->willReturn(true);
        $repositoryMock
            ->expects($this->once())
            ->method('getKnownTaxSetUuids')
            ->with($this->equalTo([static::UNKNOWN_UUID]))
            ->willReturn([]);

        $request = $this->createRequest([
            static::PRODUCT_ABSTRACT_SKU => static::UNKNOWN_UUID,
            static::OTHER_PRODUCT_ABSTRACT_SKU => static::UNKNOWN_UUID,
        ]);

        // Act
        (new ProductTaxSetValidator($repositoryMock))
            ->validateProductAbstractCollection($request, new ProductAbstractCollectionResponseTransfer());
    }

    public function testValidateDoesNotQueryWhenNoTaxSetUuidProvided(): void
    {
        // Arrange
        $repositoryMock = $this->createRepositoryMock(true, []);
        $repositoryMock->expects($this->never())->method('getKnownTaxSetUuids');

        $request = (new ProductAbstractCollectionRequestTransfer())
            ->addProductAbstract((new ProductAbstractTransfer())->setSku(static::PRODUCT_ABSTRACT_SKU));

        // Act
        $response = (new ProductTaxSetValidator($repositoryMock))
            ->validateProductAbstractCollection($request, new ProductAbstractCollectionResponseTransfer());

        // Assert
        $this->assertCount(0, $response->getErrors());
    }

    public function testValidateIgnoresEmptyTaxSetUuid(): void
    {
        // Arrange
        $repositoryMock = $this->createRepositoryMock(true, []);
        $repositoryMock->expects($this->never())->method('getKnownTaxSetUuids');

        $request = $this->createRequest([static::PRODUCT_ABSTRACT_SKU => '']);

        // Act
        $response = (new ProductTaxSetValidator($repositoryMock))
            ->validateProductAbstractCollection($request, new ProductAbstractCollectionResponseTransfer());

        // Assert
        $this->assertCount(0, $response->getErrors());
    }

    /**
     * @param array<string> $knownUuids
     *
     * @return \Spryker\Zed\TaxProductConnector\Persistence\TaxProductConnectorRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function createRepositoryMock(bool $isTaxSetUuidSupported, array $knownUuids): TaxProductConnectorRepositoryInterface
    {
        $repositoryMock = $this->createMock(TaxProductConnectorRepositoryInterface::class);
        $repositoryMock->method('isTaxSetUuidSupported')->willReturn($isTaxSetUuidSupported);
        $repositoryMock->method('getKnownTaxSetUuids')->willReturn($knownUuids);

        return $repositoryMock;
    }

    /**
     * @param array<string, string> $uuidsBySku
     *
     * @return \Generated\Shared\Transfer\ProductAbstractCollectionRequestTransfer
     */
    protected function createRequest(array $uuidsBySku): ProductAbstractCollectionRequestTransfer
    {
        $productAbstractCollectionRequestTransfer = new ProductAbstractCollectionRequestTransfer();

        foreach ($uuidsBySku as $sku => $uuid) {
            $productAbstractCollectionRequestTransfer->addProductAbstract(
                (new ProductAbstractTransfer())
                    ->setSku((string)$sku)
                    ->setTaxSetUuid($uuid),
            );
        }

        return $productAbstractCollectionRequestTransfer;
    }
}
