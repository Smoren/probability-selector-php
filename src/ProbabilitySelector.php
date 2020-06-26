<?php


namespace Smoren\ProbabilitySelector;


/**
 * Класс для вероятностного выбора сущностей
 */
class ProbabilitySelector
{
    /**
     * @var array хранилище сущностей
     */
    protected $data;

    /**
     * @var int сумма весов всех сущностей
     */
    protected $weightSum = 0;

    /**
     * @var int сумма количества использований всех сущностей
     */
    protected $usageCounterSum = 0;

    /**
     * ProbabilitySelector constructor.
     * @param array $items
     */
    public function __construct(array $items = [])
    {
        foreach($items as [$id, $weight, $usageCounter]) {
            $this->addItem($id, $weight, $usageCounter);
        }
    }

    /**
     * Добавить сущность для выбора
     * @param int $id ID сущности
     * @param float $weight вес сущности
     * @param int $usageCounter количество использований сущности
     * @return $this
     */
    public function addItem(int $id, float $weight, int $usageCounter): self
    {
        $this->data[$id] = [$weight, $usageCounter];
        $this->weightSum += $weight;
        $this->usageCounterSum += $usageCounter;

        return $this;
    }

    /**
     * Принять решение, какую сущность использовать
     * @return int ID выбранной сущности
     * @throws ProbabilitySelectorException
     */
    public function decide(): int
    {
        $maxProbability = 0;
        $maxProbabilityId = null;

        $maxDeviation = 0;
        $maxDeviationId = null;

        foreach($this->data as $id => [$weight, $usageCounter]) {
            $probability = $this->weightSum ? $weight/$this->weightSum : 0;
            $usageDistribution = $this->usageCounterSum > 0 ? $usageCounter/$this->usageCounterSum : 0;
            $deviation = $probability - $usageDistribution;

            if($probability > $maxProbability) {
                $maxProbability = $probability;
                $maxProbabilityId = $id;
            }

            if($deviation > $maxDeviation) {
                $maxDeviation = $deviation;
                $maxDeviationId = $id;
            }
        }

        if($maxProbabilityId === null) {
            throw new ProbabilitySelectorException('candidate not found');
        }

        if($maxDeviationId !== null) {
            return $maxDeviationId;
        } else {
            return $maxProbabilityId;
        }
    }

    /**
     * Инкрементирует счетчик использования сущности
     * @param int $id ID сущности
     * @return int новое значение счетчика использования
     * @throws ProbabilitySelectorException
     */
    public function incrementUsageCounter(int $id): int
    {
        $this->checkExist($id);

        $this->usageCounterSum++;

        return ++$this->data[$id][1];
    }

    /**
     * Возвращает карту сущностей по ID
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Возвращает сумму количества использований сущностей
     * @return int
     */
    public function getUsageCounterSum(): int
    {
        return $this->usageCounterSum;
    }

    /**
     * Возвращает сумму весов сущностей
     * @return int
     */
    public function getWeightSum(): int
    {
        return $this->weightSum;
    }

    /**
     * Рассчитывает величину отклонения показов сущности от заложенной весом вероятности
     * @param int $id ID сущности
     * @return float значение отклонения
     * @throws ProbabilitySelectorException
     */
    public function getDeviation(int $id): float
    {
        $this->checkExist($id);
        [$weight, $usageCounter] = $this->data[$id];

        if($this->weightSum == 0) {
            return 0;
        }

        if($this->usageCounterSum == 0) {
            return $weight/$this->weightSum;
        }

        return $usageCounter/$this->usageCounterSum - $weight/$this->weightSum;
    }

    /**
     * Проверяет сущность на наличие по ID
     * @param int $id ID сущности
     * @return $this
     * @throws ProbabilitySelectorException
     */
    protected function checkExist(int $id): self
    {
        if(!isset($this->data[$id])) {
            throw new ProbabilitySelectorException("no item found with id = {$id}");
        }

        return $this;
    }
}