<?php
/**
 * Copyright © Ergonode Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Ergonode\Core\Infrastructure\Service;

use Ergonode\Core\Infrastructure\Exception\DownloaderException;

interface DownloaderInterface
{
    /**
     * @param Header[] $headers
     * @param string[] $acceptedContentTypes
     *
     * @throws DownloaderException
     */
    public function download(string $url, array $headers = [], array $acceptedContentTypes = []): string;
}
