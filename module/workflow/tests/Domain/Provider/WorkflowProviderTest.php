<?php

/**
 * Copyright © Ergonode Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Ergonode\Workflow\Tests\Domain\Provider;

use Ergonode\Workflow\Domain\Entity\AbstractWorkflow;
use Ergonode\Workflow\Domain\Provider\WorkflowProvider;
use Ergonode\Workflow\Domain\Repository\WorkflowRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ergonode\Workflow\Domain\Query\WorkflowQueryInterface;
use Ergonode\SharedKernel\Domain\Aggregate\WorkflowId;

class WorkflowProviderTest extends TestCase
{
    /**
     * @var WorkflowRepositoryInterface|MockObject
     */
    private $repository;

    /**
     * @var WorkflowQueryInterface|MockObject
     */
    private WorkflowQueryInterface $query;

    /**
     * @var AbstractWorkflow|MockObject
     */
    private $workflow;

    /**
     * @var WorkflowId|MockObject
     */
    private $workflowId;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(WorkflowRepositoryInterface::class);
        $this->workflow = $this->createMock(AbstractWorkflow::class);
        $this->query = $this->createMock(WorkflowQueryInterface::class);
        $this->workflowId = $this->createMock(WorkflowId::class);
    }

    public function testProvideExistsObject(): void
    {
        $this->query->method('findWorkflowIdByCode')->willReturn($this->workflowId);
        $this->repository->method('load')->willReturn($this->workflow);

        $provider = new WorkflowProvider($this->repository, $this->query);
        $workflow = $provider->provide();
        $this->assertEquals($this->workflow, $workflow);
    }

    public function testProvideNonExistsObject(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->query->method('findWorkflowIdByCode')->willReturn(null);

        $provider = new WorkflowProvider($this->repository, $this->query);
        $provider->provide();
    }
}
