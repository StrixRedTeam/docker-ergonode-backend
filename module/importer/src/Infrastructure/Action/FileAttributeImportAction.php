<?php

/**
 * Copyright © Ergonode Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Ergonode\Importer\Infrastructure\Action;

use Ergonode\Attribute\Domain\Entity\Attribute\FileAttribute;
use Ergonode\Attribute\Domain\ValueObject\AttributeCode;
use Ergonode\Attribute\Domain\ValueObject\AttributeScope;
use Ergonode\Core\Domain\ValueObject\TranslatableString;
use Ergonode\Importer\Domain\Command\Import\Attribute\ImportFileAttributeCommand;
use Ergonode\Importer\Infrastructure\Exception\ImportException;
use Ergonode\SharedKernel\Domain\Aggregate\AttributeId;

class FileAttributeImportAction extends AbstractAttributeImportAction
{
    /**
     * @throws ImportException
     */
    public function action(ImportFileAttributeCommand $command): void
    {
        $this->validate($command);
        $attribute = $this->findAttribute(new AttributeCode($command->getCode()));
        if (!$attribute) {
            $attribute = new FileAttribute(
                AttributeId::generate(),
                new AttributeCode($command->getCode()),
                new TranslatableString($command->getLabel()),
                new TranslatableString($command->getHint()),
                new TranslatableString($command->getPlaceholder()),
                new AttributeScope($command->getScope())
            );
        } else {
            $this->updateAttribute($command, $attribute);
        }
        $this->processSuccessfulImport($attribute, $command);
    }
}
