<?php

/**
 * Copyright © Ergonode Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Ergonode\Workflow\Infrastructure\Persistence\Projector;

use Doctrine\DBAL\Connection;
use Ergonode\SharedKernel\Application\Serializer\SerializerInterface;
use Ergonode\Workflow\Domain\Event\Status\StatusCreatedEvent;

class DbalStatusCreatedEventProjector
{
    private const TABLE = 'status';

    private Connection $connection;

    private SerializerInterface $serializer;


    public function __construct(Connection $connection, SerializerInterface $serializer)
    {
        $this->connection = $connection;
        $this->serializer = $serializer;
    }

    /**
     * {@inheritDoc}
     */
    public function __invoke(StatusCreatedEvent $event): void
    {
        $sql = 'INSERT INTO '.self::TABLE.'(id, code, name, description, color, index)
                VALUES(:id, :code, :name, :description, :color, COALESCE((SELECT MAX(index) + 1 FROM status),0))';
        $this->connection->executeQuery(
            $sql,
            [
                'id' => $event->getAggregateId()->getValue(),
                'code' => $event->getCode(),
                'name' => $this->serializer->serialize($event->getName()->getTranslations()),
                'description' => $this->serializer->serialize($event->getDescription()->getTranslations()),
                'color' => $event->getColor()->getValue(),
            ],
        );
    }
}
