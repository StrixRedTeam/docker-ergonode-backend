<?php

/**
 * Copyright © Ergonode Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Ergonode\Product\Domain\Factory;

use Ergonode\Product\Domain\Entity\AbstractProduct;
use Ergonode\Product\Domain\ValueObject\Sku;
use Ergonode\Product\Infrastructure\Provider\ProductStrategyProvider;
use Ergonode\SharedKernel\Domain\Aggregate\ProductId;
use Ergonode\SharedKernel\Domain\Aggregate\TemplateId;

class ProductFactory implements ProductFactoryInterface
{
    private ProductStrategyProvider $productProvider;

    public function __construct(ProductStrategyProvider $productProvider)
    {
        $this->productProvider = $productProvider;
    }

    public function create(
        string $type,
        ProductId $id,
        Sku $sku,
        TemplateId $templateId,
        array $categories = [],
        array $attributes = []
    ): AbstractProduct {
        return $this->productProvider->provide($type)->build($id, $sku, $templateId, $categories, $attributes);
    }
}
