<?php

/**
 * Copyright © Ergonode Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Ergonode\Migration;

use Doctrine\DBAL\Schema\Schema;
use Ergonode\Attribute\Domain\Event\Attribute\AttributeOptionAddedEvent;
use Ergonode\Attribute\Domain\Event\Attribute\AttributeOptionMovedEvent;
use Ergonode\Attribute\Domain\Event\Attribute\AttributeOptionRemovedEvent;
use Ergonode\Attribute\Domain\Event\Option\OptionCreatedEvent;
use Ramsey\Uuid\Uuid;

final class Version20211102105000 extends AbstractErgonodeMigration
{
    private const EVENTS = [
        AttributeOptionAddedEvent::class => 'Attribute option added',
        AttributeOptionRemovedEvent::class => 'Attribute option removed',
        AttributeOptionMovedEvent::class => 'Attribute option moved',
    ];

    /**
     * @throws \Exception
     */
    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE attribute_options (   
                attribute_id UUID NOT NULL, 
                option_id UUID NOT NULL,        
                index integer NOT NULL,                                         
                PRIMARY KEY(attribute_id, option_id, index)
            )
        ');

        $this->addSql(' ALTER TABLE attribute_option DROP CONSTRAINT attribute_option_pkey ');
        $this->addSql(' ALTER TABLE attribute_option ADD CONSTRAINT attribute_option_pkey PRIMARY KEY (id)');

        $this->addSql('
            ALTER TABLE attribute_options 
                ADD CONSTRAINT attribute_options_attribute_id_fk 
                    FOREIGN KEY (attribute_id) REFERENCES attribute ON UPDATE CASCADE ON DELETE CASCADE');

        $this->addSql('
            ALTER TABLE attribute_options
                ADD CONSTRAINT attribute_options_option_id_fk
                    FOREIGN KEY (option_id) REFERENCES attribute_option ON UPDATE CASCADE ON DELETE RESTRICT');

        $ids = [];
        foreach (self::EVENTS as $class => $translation) {
            $id = Uuid::uuid4()->toString();
            $this->addSql(
                'INSERT INTO event_store_event (id, event_class, translation_key) VALUES (?,?,?)',
                [$id, $class, $translation]
            );
            $ids[$class] = $id;
        }

        $this->migrateEvents($ids[AttributeOptionAddedEvent::class], $this->getRows());
        $this->dropPayloadAttributeId();

        $this->addSql('ALTER TABLE  attribute_option DROP COLUMN attribute_id');
    }

    private function migrateEvents(string $eventId, array $data): void
    {
        $recordedAt = new \DateTime('now');
        $seq = [];
        $index = [];

        foreach ($data as $row) {
            $payload = json_encode(
                [
                    "id" => $row['attribute_id'],
                    "optionId" => $row['aggregate_id'],
                    "index" => $row['row_number'] - 1,
                ],
                JSON_UNESCAPED_UNICODE
            );

            if (!isset($seq[$row['attribute_id']])) {
                $seq[$row['attribute_id']] = $this->getMaxSequence($row['attribute_id']) + 1;
            }

            $sequence = $seq[$row['attribute_id']]++;
            $this->insertEvent($row['attribute_id'], $sequence, $eventId, $payload, $recordedAt);
            $this->clearSnapshot($row['attribute_id']);
            $this->clearSnapshot($row['aggregate_id']);

            //projection
            if (!isset($index[$row['attribute_id']])) {
                $index[$row['attribute_id']] = 0;
            }
            $i = $index[$row['attribute_id']]++;
            $this->addSql(
                'INSERT INTO attribute_options (attribute_id, option_id, index) VALUES (?,?,?)',
                [$row['attribute_id'], $row['aggregate_id'], $i]
            );
        }
    }

    private function getMaxSequence(string $aggregateId): int
    {
        $max = $this->connection->executeQuery(
            'SELECT MAX("sequence") FROM event_store WHERE aggregate_id = :aggregateId',
            [
                'aggregateId' => $aggregateId,
            ]
        )->fetchOne();

        return $max ?: 0;
    }

    private function insertEvent(
        string $optionId,
        int $sequence,
        string $eventId,
        string $payload,
        \DateTime $recordedAt
    ): void {
        $this->addSql(
            'INSERT INTO event_store (aggregate_id, sequence, event_id, payload, recorded_at) VALUES (?,?,?,?,?)',
            [
                $optionId,
                $sequence,
                $eventId,
                $payload,
                $recordedAt->format('Y-m-d H:i:s.u'),
            ]
        );
    }

    private function clearSnapshot(string $id): void
    {
        $this->connection->executeQuery(
            'DELETE FROM event_store_snapshot WHERE aggregate_id = :id',
            [
                'id' => $id,
            ],
        );
    }

    private function getRows(): array
    {
        return $this->connection
            ->executeQuery(
                '
                SELECT 
                    es.aggregate_id,
                    es.payload->>\'attribute_id\' as attribute_id,
                    ROW_NUMBER() OVER (PARTITION BY es.payload->>\'attribute_id\' ORDER BY es.id)
                FROM event_store es 
                JOIN event_store_event ese ON ese.id = es.event_id 
                WHERE ese.event_class = :class
                ORDER BY attribute_id ASC, es.id ASC
                ',
                [
                    'class' => OptionCreatedEvent::class,
                ]
            )
            ->fetchAllAssociative();
    }

    private function dropPayloadAttributeId(): void
    {
        $this->addSql(
            '
                UPDATE event_store
                SET payload = payload - \'attribute_id\'
                FROM event_store_event 
                WHERE  event_store_event.id = event_store.event_id AND event_class = :class
                ',
            [
                'class' => OptionCreatedEvent::class,
            ]
        );
    }
}
