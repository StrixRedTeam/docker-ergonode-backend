<?php

/**
 * Copyright © Ergonode Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Ergonode\Multimedia\Tests\Application\Service;

use Ergonode\Multimedia\Application\Service\DuplicateFilenameSuffixGeneratingService;
use PHPUnit\Framework\TestCase;

class DuplicateFilenameSuffixGeneratingServiceTest extends TestCase
{
    /**
     * @dataProvider dataProvider
     */
    public function testGenerateSuffix(string $name, string $extension, int $iterationIndex, string $expected): void
    {
        $generator = new DuplicateFilenameSuffixGeneratingService();

        $result = $generator->generateSuffix($name, $extension, $iterationIndex);
        $this->assertSame($result, $expected);
    }

    /**
     * @return array
     */
    public function dataProvider(): array
    {
        return [
            [
                'name' => str_repeat('a', 15).'.jpg',
                'extension' => 'jpg',
                'iterationIndex' => 2,
                'expected' => str_repeat('a', 15).'(2).jpg',
            ],
            [
                'name' => str_repeat('b', 15).'.jpg',
                'extension' => 'jpg',
                'iterationIndex' => 123,
                'expected' => str_repeat('b', 15).'(123).jpg',
            ],
            [
                'name' => str_repeat('b', 15),
                'extension' => '',
                'iterationIndex' => 123,
                'expected' => str_repeat('b', 15).'(123)',
            ],
            [
                'name' => str_repeat('b', 15),
                'extension' => 'pdf',
                'iterationIndex' => 123,
                'expected' => str_repeat('b', 15).'(123)',
            ],
            [
                'name' => str_repeat('c', 130).'.jpg',
                'extension' => 'jpg',
                'iterationIndex' => 2,
                'expected' => str_repeat('c', 121).'(2).jpg',
            ],
            [
                'name' => str_repeat('d', 130).'.jpg',
                'extension' => 'jpg',
                'iterationIndex' => 123,
                'expected' => str_repeat('d', 119).'(123).jpg',
            ],
            [
                'name' => str_repeat('d', 130),
                'extension' => '',
                'iterationIndex' => 123,
                'expected' => str_repeat('d', 123).'(123)',
            ],
            [
                'name' => str_repeat('d', 130),
                'extension' => 'pdf',
                'iterationIndex' => 123,
                'expected' => str_repeat('d', 123).'(123)',
            ],
            [
                'name' => str_repeat('©', 130).'.jpg',
                'extension' => 'jpg',
                'iterationIndex' => 123,
                'expected' => str_repeat('©', 119).'(123).jpg',
            ],
        ];
    }
}
