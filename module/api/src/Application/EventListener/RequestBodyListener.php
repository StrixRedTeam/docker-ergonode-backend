<?php

/**
 * Copyright © Ergonode Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Ergonode\Api\Application\EventListener;

use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class RequestBodyListener
{
    private const METHODS = [
        Request::METHOD_PUT,
        Request::METHOD_POST,
        Request::METHOD_PATCH,
        Request::METHOD_DELETE,
    ];

    private const CONTENT_TYPES = ['json'];

    public function __invoke(RequestEvent $event): void
    {
        $request = $event->getRequest();

        $contentType = $request->getContentType() ?? 'json';
        $method = $request->getMethod();
        $content = $request->getContent();

        if (empty($content) ||
            !in_array($contentType, self::CONTENT_TYPES, true) ||
            !in_array($method, self::METHODS, true)) {
            return;
        }

        $data = json_decode($content, true);

        if (!is_array($data)) {
            if ('json' === $request->getContentType()) {
                throw new BadRequestHttpException('Request body is not a valid json.');
            }

            return;
        }

        $request->request = new ParameterBag($data);
    }
}
