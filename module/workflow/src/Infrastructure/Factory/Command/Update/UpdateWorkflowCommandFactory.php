<?php
/**
 * Copyright © Ergonode Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Ergonode\Workflow\Infrastructure\Factory\Command\Update;

use Ergonode\SharedKernel\Domain\Aggregate\RoleId;
use Ergonode\Workflow\Domain\Command\Workflow\UpdateWorkflowCommandInterface;
use Symfony\Component\Form\FormInterface;
use Ergonode\Workflow\Infrastructure\Factory\Command\UpdateWorkflowCommandFactoryInterface;
use Ergonode\Workflow\Domain\Entity\Workflow;
use Ergonode\Workflow\Application\Form\Model\Workflow\WorkflowFormModel;
use Ergonode\SharedKernel\Domain\Aggregate\StatusId;
use Ergonode\SharedKernel\Domain\Aggregate\WorkflowId;
use Ergonode\Workflow\Domain\Command\Workflow\UpdateWorkflowCommand;

class UpdateWorkflowCommandFactory implements UpdateWorkflowCommandFactoryInterface
{
    public function support(string $type): bool
    {
        return $type === Workflow::TYPE;
    }

    public function create(WorkflowId $id, FormInterface $form): UpdateWorkflowCommandInterface
    {
        /** @var WorkflowFormModel $data */
        $data = $form->getData();
        $statuses = [];
        foreach ($data->statuses as $status) {
            $statuses[] = new StatusId($status);
        }
        $transitions = [];
        foreach ($data->transitions as $key => $transitionModel) {
            $transitions[$key]['from'] = new StatusId($transitionModel->from);
            $transitions[$key]['to'] = new StatusId($transitionModel->to);
            foreach ($transitionModel->roles as $role) {
                $transitions[$key]['roles'][] = new RoleId($role);
            }
        }
        $defaultStatus = null;
        if ($data->defaultId) {
            $defaultStatus = new StatusId($data->defaultId);
        }

        return new UpdateWorkflowCommand(
            $id,
            $statuses,
            $transitions,
            $defaultStatus,
        );
    }
}
