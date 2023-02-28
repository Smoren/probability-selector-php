<?php

declare(strict_types=1);

namespace Smoren\ProbabilitySelector;

/**
 * Probability-based selection manager.
 *
 * @template T
 *
 * @implements \IteratorAggregate<int, T>
 */
class ProbabilitySelector implements \IteratorAggregate
{
    /**
     * @var array<T> data storage
     */
    protected array $data = [];

    /**
     * @var array<array{float, int}>
     */
    protected array $probabilities = [];

    /**
     * @var float sum of all the weights of data
     */
    protected float $weightSum = 0;

    /**
     * @var int usage counters sum of data
     */
    protected int $totalUsageCounter = 0;

    /**
     * ProbabilitySelector constructor.
     *
     * @param array<array{T, float}|array{T, float, int}> $data
     */
    public function __construct(array $data = [])
    {
        foreach ($data as $item) {
            if (\count($item) === 2) {
                $item[] = 0;
            }

            /** @var array{T, float, int} $item */
            [$datum, $weight, $usageCounter] = $item;
            $this->addItem($datum, $weight, $usageCounter);
        }
    }

    /**
     * Adds datum to the select list.
     *
     * @param T $datum datum to add
     * @param float $weight weight of datum
     * @param int $usageCounter initial usage counter value for datum
     *
     * @return $this
     */
    public function addItem($datum, float $weight, int $usageCounter): self
    {
        if ($weight <= 0) {
            throw new \InvalidArgumentException('Weight cannot be negative');
        }

        $this->data[] = $datum;
        $this->probabilities[] = [$weight, $usageCounter];
        $this->weightSum += $weight;
        $this->totalUsageCounter += $usageCounter;

        return $this;
    }

    /**
     * Chooses and returns datum from select list, marks it used.
     *
     * @return T chosen datum
     *
     * @throws \LengthException when selectable list is empty
     */
    public function decide()
    {
        $maxScore = -INF;
        $maxScoreWeight = -INF;
        $maxScoreId = null;

        if (\count($this->probabilities) === 0) {
            throw new \LengthException('Candidate not found in empty list');
        }

        foreach ($this->probabilities as $id => [$weight, $usageCounter]) {
            $score = $weight / ($usageCounter + 1);

            if ($this->areFloatsEqual($score, $maxScore) && $weight > $maxScoreWeight || $score > $maxScore) {
                $maxScore = $score;
                $maxScoreWeight = $weight;
                $maxScoreId = $id;
            }
        }

        /** @var int $maxScoreId */
        $this->incrementUsageCounter($maxScoreId);
        return $this->data[$maxScoreId];
    }

    /**
     * Returns iterator to get decisions sequence.
     *
     * @param int|null $limit
     *
     * @return \Generator
     */
    public function getIterator(?int $limit = null): \Generator
    {
        for ($i = 0; $limit === null || $i < $limit; ++$i) {
            yield $this->totalUsageCounter => $this->decide();
        }
    }

    /**
     * Exports data with probabilities and usage counters.
     *
     * @return array<array{T, float, int}>
     */
    public function export(): array
    {
        return array_map(fn ($datum, $config) => [$datum, ...$config], $this->data, $this->probabilities);
    }

    /**
     * Increments usage counter of datum by its ID.
     *
     * @param int $id datum ID
     *
     * @return int current value of usage counter
     */
    protected function incrementUsageCounter(int $id): int
    {
        $this->totalUsageCounter++;
        return ++$this->probabilities[$id][1];
    }

    /**
     * Returns true if parameters are equal.
     *
     * @param float $lhs
     * @param float $rhs
     *
     * @return bool
     */
    protected function areFloatsEqual(float $lhs, float $rhs): bool
    {
        return \abs($lhs - $rhs) < PHP_FLOAT_EPSILON;
    }
}
