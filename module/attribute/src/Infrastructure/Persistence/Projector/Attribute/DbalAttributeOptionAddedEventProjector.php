<?php

/**
 * Copyright © Ergonode Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Ergonode\Attribute\Infrastructure\Persistence\Projector\Attribute;

use Ergonode\Attribute\Domain\Event\Attribute\AttributeOptionAddedEvent;

class DbalAttributeOptionAddedEventProjector extends AbstractDbalAttributeOptionEventProjector
{
    public function __invoke(AttributeOptionAddedEvent $event): void
    {
        $index = $event->getIndex();

        $this->shiftPosition($event->getAggregateId(), $index);

        $this->connection->insert(
            self::TABLE,
            [
                'attribute_id' => $event->getAggregateId()->getValue(),
                'option_id' => $event->getOptionId()->getValue(),
                'index' => $index,
            ]
        );
    }
}
