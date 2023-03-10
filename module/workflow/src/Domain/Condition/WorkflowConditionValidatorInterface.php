<?php

/**
 * Copyright © Ergonode Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Ergonode\Workflow\Domain\Condition;

use Symfony\Component\Validator\Constraint;

interface WorkflowConditionValidatorInterface
{
    public function supports(string $type): bool;

    public function build(): Constraint;
}
