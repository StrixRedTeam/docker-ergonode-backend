<?php

/**
 * Copyright © Ergonode Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Ergonode\Workflow\Application\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class StatusIdsContainAll extends Constraint
{
    public string $message = 'Doesn\'t contain all status ids';
}
