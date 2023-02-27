<?php

declare(strict_types=1);

namespace Smoren\ProbabilitySelector\Tests\Unit;

use Codeception\Test\Unit;
use Smoren\ProbabilitySelector\ProbabilitySelector;

class ProbabilitySelectorTest extends Unit
{
    /**
     * @dataProvider dataProviderForDemo
     * @dataProvider dataProviderForZeroInitialUsageCount
     * @dataProvider dataProviderForSpecificUsageCount
     * @param array $input
     * @param int $steps
     * @param array $expected
     * @return void
     */
    public function testDecisionSequencesLimited(array $input, int $steps, array $expected): void
    {
        // Given
        $ps = new ProbabilitySelector($input);
        $result = [];

        // When
        foreach ($ps as $datum) {
            $result[] = $datum;

            if (--$steps === 0) {
                break;
            }
        }

        // Then
        $this->assertEquals($expected, $result);
    }

    /**
     * @dataProvider dataProviderForDemo
     * @dataProvider dataProviderForZeroInitialUsageCount
     * @dataProvider dataProviderForSpecificUsageCount
     * @param array $input
     * @param int $steps
     * @param array $expected
     * @return void
     */
    public function testDecisionSequencesUnlimited(array $input, int $steps, array $expected): void
    {
        // Given
        $ps = new ProbabilitySelector($input);
        $result = [];

        // When
        foreach ($ps->getIterator($steps) as $datum) {
            $result[] = $datum;
        }

        // Then
        $this->assertEquals($expected, $result);
    }

    public function dataProviderForDemo(): array
    {
        return [
            [
                [
                    ['first', 1, 0],
                    ['second', 2, 0],
                    ['third', 3, 4],
                ],
                15,
                ['second', 'second', 'first', 'second', 'third', 'third', 'second', 'first', 'third', 'second', 'third', 'third', 'second', 'first', 'third'],
            ],
        ];
    }

    public function dataProviderForZeroInitialUsageCount(): array
    {
        return [
            [
                [
                    ['a', 1],
                ],
                3,
                ['a', 'a', 'a'],
            ],
            [
                [
                    ['a', 0.5],
                ],
                3,
                ['a', 'a', 'a'],
            ],
            [
                [
                    ['a', 1],
                    ['b', 1],
                ],
                5,
                ['a', 'b', 'a', 'b', 'a'],
            ],
            [
                [
                    ['a', 2],
                    ['b', 1],
                ],
                6,
                ['a', 'a', 'b', 'a', 'a', 'b'],
            ],
            [
                [
                    ['a', 1],
                    ['b', 2],
                ],
                6,
                ['b', 'b', 'a', 'b', 'b', 'a'],
            ],
            [
                [
                    ['a', 1],
                    ['b', 2],
                    ['c', 1],
                ],
                10,
                ['b', 'b', 'a', 'c', 'b', 'b', 'a', 'c', 'b', 'b'],
            ],
            [
                [
                    ['a', 0.1],
                    ['b', 0.2],
                    ['c', 0.1],
                ],
                10,
                ['b', 'b', 'a', 'c', 'b', 'b', 'a', 'c', 'b', 'b'],
            ],
            [
                [
                    ['a', 1],
                    ['b', 2],
                    ['c', 3],
                ],
                12,
                ['c', 'b', 'c', 'c', 'b', 'a', 'c', 'b', 'c', 'c', 'b', 'a'],
            ],
            [
                [
                    ['a', 2],
                    ['b', 4],
                    ['c', 6],
                ],
                12,
                ['c', 'b', 'c', 'c', 'b', 'a', 'c', 'b', 'c', 'c', 'b', 'a'],
            ],
            [
                [
                    ['a', 0.2],
                    ['b', 0.4],
                    ['c', 0.6],
                ],
                12,
                ['c', 'b', 'c', 'c', 'b', 'a', 'c', 'b', 'c', 'c', 'b', 'a'],
            ],
            [
                [
                    ['a', 1],
                    ['b', 2],
                    ['c', 4],
                ],
                12,
                ['c', 'c', 'b', 'c', 'c', 'b', 'a', 'c', 'c', 'b', 'c', 'c'],
            ],
        ];
    }

    public function dataProviderForSpecificUsageCount(): array
    {
        return [
            [
                [
                    ['a', 1],
                    ['b', 1, 2],
                ],
                10,
                ['a', 'a', 'a', 'b', 'a', 'b', 'a', 'b', 'a', 'b'],
            ],
            [
                [
                    ['a', 1],
                    ['b', 1, 3],
                ],
                10,
                ['a', 'a', 'a', 'a', 'b', 'a', 'b', 'a', 'b', 'a'],
            ],
            [
                [
                    ['a', 1],
                    ['b', 2, 3],
                ],
                10,
                ['a', 'b', 'a', 'b', 'b', 'a', 'b', 'b', 'a', 'b'],
            ],
        ];
    }

    /**
     * @dataProvider dataProviderForAxiomatic
     * @param array $input
     * @param int $cyclesCount
     * @return void
     */
    public function testAxiomatic(array $input, int $cyclesCount)
    {
        // Given
        $ps = new ProbabilitySelector($input);
        $countMap = \array_map(fn ($item) => 0, \array_flip(\array_map(fn ($item) => $item[0], $input)));
        $weightSum = \array_sum(\array_map(fn ($item) => $item[1], $input));
        $count = \round($cyclesCount * $weightSum, 4);

        // When
        for ($i = 0; $i < $count; ++$i) {
            $datum = $ps->decide();
            $countMap[$datum]++;
        }

        $result = \array_map(fn (int $count, array $inputItem) => $count / $inputItem[1], $countMap, $input);
        $result = \array_unique($result);

        // Then
        $this->assertCount(1, $result);
    }

    public function dataProviderForAxiomatic(): array
    {
        return [
            [
                [
                    ['a', 1],
                    ['b', 2],
                    ['c', 3],
                ],
                1,
            ],
            [
                [
                    ['a', 1],
                    ['b', 2],
                    ['c', 3],
                ],
                10,
            ],
            [
                [
                    ['a', 1],
                    ['b', 2],
                    ['c', 3],
                ],
                100,
            ],
            [
                [
                    ['a', 2],
                    ['b', 4],
                    ['c', 6],
                ],
                100,
            ],
            [
                [
                    ['a', 0.1],
                    ['b', 0.2],
                    ['c', 0.3],
                ],
                100,
            ],
            [
                [
                    ['a', 1],
                    ['b', 2],
                    ['c', 4],
                ],
                100,
            ],
            [
                [
                    ['a', 1],
                    ['b', 1],
                    ['c', 3],
                ],
                100,
            ],
            [
                [
                    ['a', 0.1],
                    ['b', 1],
                    ['c', 3],
                ],
                100,
            ],
            [
                [
                    ['a', 0.1],
                    ['b', 1],
                    ['c', 30],
                ],
                100,
            ],
            [
                [
                    ['a', 0.1],
                    ['b', 2],
                    ['c', 0.5],
                    ['d', 3],
                    ['e', 2.2],
                    ['f', 3],
                    ['g', 30],
                    ['h', 30],
                ],
                100,
            ],
        ];
    }

    /**
     * @return void
     */
    public function testErrorOnEmptyList(): void
    {
        // Given
        $ps = new ProbabilitySelector();

        // Then
        $this->expectException(\LengthException::class);
        $this->expectExceptionMessage('Candidate not found in empty list');

        // When
        $ps->decide();
    }

    /**
     * @dataProvider dataProviderForErrorOnNegativeWeight
     * @param array $input
     * @return void
     */
    public function testErrorOnNegativeWeight(array $input): void
    {
        // Then
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Weight cannot be negative');

        // When
        new ProbabilitySelector($input);
    }

    public function dataProviderForErrorOnNegativeWeight(): array
    {
        return [
            [
                [
                    ['a', -1],
                ],
            ],
            [
                [
                    ['a', -1, 0],
                ],
            ],
            [
                [
                    ['a', -0.1],
                ],
            ],
            [
                [
                    ['a', 1],
                    ['b', -0.2],
                    ['c', 3],
                ],
            ],
        ];
    }
}
