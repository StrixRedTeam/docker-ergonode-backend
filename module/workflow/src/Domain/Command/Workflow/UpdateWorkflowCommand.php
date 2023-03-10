<?php

/**
 * Copyright © Ergonode Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Ergonode\Workflow\Domain\Command\Workflow;

use Ergonode\SharedKernel\Domain\Aggregate\WorkflowId;
use Webmozart\Assert\Assert;
use Ergonode\SharedKernel\Domain\Aggregate\StatusId;

class UpdateWorkflowCommand implements UpdateWorkflowCommandInterface
{
    private WorkflowId $id;

    /**
     * @var StatusId[]
     */
    private array $statuses;

    private array $transitions;

    private ?StatusId $defaultStatus;

    /**
     * @param StatusId[] $statuses
     */
    public function __construct(
        WorkflowId $id,
        array $statuses = [],
        array $transitions = [],
        ?StatusId $defaultStatus = null
    ) {
        Assert::allIsInstanceOf($statuses, StatusId::class);

        $this->id = $id;
        $this->defaultStatus = $defaultStatus;
        $this->statuses = $statuses;
        $this->transitions = $transitions;
    }

    public function getId(): WorkflowId
    {
        return $this->id;
    }

    /**
     * @return StatusId[]
     */
    public function getStatuses(): array
    {
        return $this->statuses;
    }

    /**
     * @return array
     */
    public function getTransitions(): array
    {
        return $this->transitions;
    }

    public function getDefaultStatus(): ?StatusId
    {
        return $this->defaultStatus;
    }
}
