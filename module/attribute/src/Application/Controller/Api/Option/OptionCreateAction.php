<?php

/**
 * Copyright © Ergonode Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Ergonode\Attribute\Application\Controller\Api\Option;

use Ergonode\Api\Application\Exception\FormValidationHttpException;
use Ergonode\Attribute\Application\Form\Model\Option\SimpleOptionModel;
use Ergonode\Attribute\Application\Form\SimpleOptionForm;
use Ergonode\Attribute\Domain\Command\Option\CreateOptionCommand;
use Ergonode\Attribute\Domain\ValueObject\OptionKey;
use Ergonode\Core\Domain\ValueObject\TranslatableString;
use Ergonode\SharedKernel\Domain\AggregateId;
use Ergonode\SharedKernel\Domain\Bus\CommandBusInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Swagger\Annotations as SWG;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\PropertyAccess\Exception\InvalidPropertyPathException;
use Symfony\Component\Routing\Annotation\Route;
use Ergonode\Attribute\Domain\Entity\Attribute\AbstractOptionAttribute;

/**
 * @Route(
 *     name="ergonode_option_create",
 *     path="/attributes/{attribute}/options",
 *     methods={"POST"},
 *     requirements={
 *        "attribute" = "[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}"
 *     }
 * )
 */
class OptionCreateAction
{
    private FormFactoryInterface $formFactory;

    private CommandBusInterface $commandBus;

    public function __construct(FormFactoryInterface $formFactory, CommandBusInterface $commandBus)
    {
        $this->formFactory = $formFactory;
        $this->commandBus = $commandBus;
    }

    /**
     * @IsGranted("ERGONODE_ROLE_ATTRIBUTE_POST_OPTION")
     *
     * @SWG\Tag(name="Attribute")
     * @SWG\Parameter(
     *     name="language",
     *     in="path",
     *     type="string",
     *     description="Language code",
     *     default="en_GB"
     * )
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     description="Add attribute",
     *     required=true,
     *     @SWG\Schema(ref="#/definitions/option")
     * )
     * @SWG\Parameter(
     *     name="language",
     *     in="path",
     *     type="string",
     *     required=true,
     *     default="en_GB",
     *     description="Language Code",
     * )
     * @SWG\Response(
     *     response=201,
     *     description="Returns attribute ID",
     * )
     * @SWG\Response(
     *     response=400,
     *     description="Validation error",
     *     @SWG\Schema(ref="#/definitions/validation_error_response")
     * )
     *
     * @throws \Exception
     */
    public function __invoke(AbstractOptionAttribute $attribute, Request $request): AggregateId
    {
        try {
            $model = new SimpleOptionModel($attribute->getId());
            $form = $this->formFactory->create(SimpleOptionForm::class, $model);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                /** @var SimpleOptionModel $data */
                $data = $form->getData();

                $command = new CreateOptionCommand(
                    $attribute->getId(),
                    new OptionKey($data->code),
                    new TranslatableString($data->label),
                    $data->after,
                    $data->positionId ? new AggregateId($data->positionId) : null,
                );

                $this->commandBus->dispatch($command);

                return $command->getId();
            }
        } catch (InvalidPropertyPathException $exception) {
            throw new BadRequestHttpException('Invalid JSON format');
        }

        throw new FormValidationHttpException($form);
    }
}
