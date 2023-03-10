<?php

/**
 * Copyright © Ergonode Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Ergonode\Value\Infrastructure\Persistence\Projector;

use Doctrine\DBAL\Connection;
use Ergonode\SharedKernel\Application\Serializer\SerializerInterface;
use Ergonode\Value\Domain\Event\ValueChangedEvent;
use Ramsey\Uuid\Uuid;
use Ergonode\Attribute\Domain\Query\AttributeQueryInterface;
use Webmozart\Assert\Assert;

class DbalValueChangedEventProjector
{
    private const NAMESPACE = '0cc20207-d1b7-460b-8ef6-6898d00de4c0';
    private const VALUE_TABLE = 'attribute_value';
    private const RELATION_TABLE = 'entity_attribute_value';

    private Connection $connection;

    private SerializerInterface $serializer;

    private AttributeQueryInterface $attributeQuery;

    public function __construct(
        Connection $connection,
        SerializerInterface $serializer,
        AttributeQueryInterface $attributeQuery
    ) {
        $this->connection = $connection;
        $this->serializer = $serializer;
        $this->attributeQuery = $attributeQuery;
    }

    /**
     * @throws \Throwable
     */
    public function __invoke(ValueChangedEvent $event): void
    {
        $this->connection->transactional(function () use ($event): void {
            $attributeId = $this->attributeQuery->findAttributeIdByCode($event->getAttributeCode());
            Assert::notNull($attributeId);
            $type = get_class($event->getTo());
            $newValue = $this->serializer->serialize($event->getTo());
            $oldValue = $this->serializer->serialize($event->getTo());

            $newValueId = Uuid::uuid5(self::NAMESPACE, $newValue);
            $oldValueId = Uuid::uuid5(self::NAMESPACE, $oldValue);

            $qb = $this->connection->createQueryBuilder();
            $result = $qb->select('*')
                ->from(self::VALUE_TABLE)
                ->where($qb->expr()->eq('id', ':id'))
                ->setParameter(':id', $newValueId->toString())
                ->execute()
                ->fetch();

            if (false === $result) {
                $this->connection->executeQuery(
                    sprintf(
                        'INSERT INTO %s (id, type, value) VALUES (?, ?, ?) ON CONFLICT DO NOTHING',
                        self::VALUE_TABLE
                    ),
                    [$newValueId->toString(), $type, $newValueId]
                );
            }

            $this->connection->insert(
                self::RELATION_TABLE,
                [
                    'entity_id' => $event->getAggregateId()->getValue(),
                    'attribute_id' => $attributeId->getValue(),
                    'value_id' => $newValueId->toString(),
                ]
            );

            $this->connection->delete(
                self::RELATION_TABLE,
                [
                    'entity_id' => $event->getAggregateId()->getValue(),
                    'attribute_id' => $attributeId->getValue(),
                    'value_id' => $oldValueId->toString(),
                ]
            );
        });
    }
}
