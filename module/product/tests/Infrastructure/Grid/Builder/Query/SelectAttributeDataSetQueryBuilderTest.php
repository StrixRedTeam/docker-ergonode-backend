<?php
/**
 * Copyright © Ergonode Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Ergonode\Product\Tests\Infrastructure\Grid\Builder\Query;

use Doctrine\DBAL\Query\QueryBuilder;
use Ergonode\Attribute\Domain\Entity\AbstractAttribute;
use Ergonode\Attribute\Domain\Entity\Attribute\SelectAttribute;
use Ergonode\Attribute\Domain\Entity\Attribute\DateAttribute;
use Ergonode\Core\Domain\ValueObject\Language;
use Ergonode\Product\Infrastructure\Grid\Builder\Query\SelectAttributeDataSetQueryBuilder;
use Ergonode\Product\Infrastructure\Strategy\ProductAttributeLanguageResolver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ergonode\Core\Domain\Query\LanguageQueryInterface;

class SelectAttributeDataSetQueryBuilderTest extends TestCase
{
    /**
     * @var DateAttribute|MockObject
     */
    private $attribute;

    /**
     * @var QueryBuilder|MockObject
     */
    private $queryBuilder;

    /**
     * @var Language|MockObject
     */
    private $language;

    /**
     * @var LanguageQueryInterface|MockObject
     */
    private LanguageQueryInterface $query;

    private ProductAttributeLanguageResolver $resolver;

    protected function setUp(): void
    {
        $this->attribute = $this->createMock(SelectAttribute::class);
        $this->queryBuilder = $this->createMock(QueryBuilder::class);
        $this->language = $this->createMock(Language::class);
        $this->query = $this->createMock(LanguageQueryInterface::class);
        $this->query->method('getLanguageNodeInfo')->willReturn(['lft' => 1, 'rgt' => 10, 'code' => 'en_GB']);
        $this->resolver = new ProductAttributeLanguageResolver($this->query);
    }

    public function testIsSupported(): void
    {
        $builder = new SelectAttributeDataSetQueryBuilder($this->query, $this->resolver);
        $this->assertTrue($builder->supports($this->attribute));
    }

    public function testIsNotSupported(): void
    {
        $builder = new SelectAttributeDataSetQueryBuilder($this->query, $this->resolver);
        $this->assertFalse($builder->supports($this->createMock(AbstractAttribute::class)));
    }

    public function testAddQuerySelect(): void
    {
        $this->queryBuilder->expects($this->once())->method('addSelect');
        $builder = new SelectAttributeDataSetQueryBuilder($this->query, $this->resolver);
        $builder->addSelect($this->queryBuilder, 'any key', $this->attribute, $this->language);
    }
}
