<?php

/**
 * Copyright © Ergonode Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Ergonode\Workflow\Tests\Domain\Command\Workflow;

use Ergonode\SharedKernel\Domain\Aggregate\ConditionSetId;
use Ergonode\SharedKernel\Domain\Aggregate\RoleId;
use Ergonode\Workflow\Domain\Command\Workflow\UpdateWorkflowTransitionCommand;
use Ergonode\SharedKernel\Domain\Aggregate\WorkflowId;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ergonode\SharedKernel\Domain\Aggregate\StatusId;

class UpdateWorkflowTransitionCommandTest extends TestCase
{
    /**
     * @throws \Exception
     */
    public function testCommandCreating(): void
    {
        /** @var WorkflowId $workflowId */
        $workflowId = $this->createMock(WorkflowId::class);

        /** @var StatusId | MockObject $from */
        $from = $this->createMock(StatusId::class);

        /** @var StatusId | MockObject $to */
        $to = $this->createMock(StatusId::class);

        /** @var RoleId[] | MockObject[] $roleIds */
        $roleIds = [$this->createMock(RoleId::class)];

        /** @var ConditionSetId | MockObject $conditionSetId */
        $conditionSetId = $this->createMock(ConditionSetId::class);

        $command = new UpdateWorkflowTransitionCommand(
            $workflowId,
            $from,
            $to,
            $roleIds,
            $conditionSetId
        );

        $this->assertSame($workflowId, $command->getWorkflowId());
        $this->assertSame($from, $command->getSource());
        $this->assertSame($to, $command->getDestination());
        $this->assertSame($from, $command->getFrom());
        $this->assertSame($to, $command->getTo());
        $this->assertSame($roleIds, $command->getRoleIds());
        $this->assertSame($conditionSetId, $command->getConditionSetId());
    }
}
