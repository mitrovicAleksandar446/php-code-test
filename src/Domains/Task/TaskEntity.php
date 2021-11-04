<?php
declare(strict_types=1);

namespace Tymeshift\PhpTest\Domains\Task;

use Tymeshift\PhpTest\Interfaces\EntityInterface;

class TaskEntity implements EntityInterface
{
    public const TYPE = 'Task';

    private int $id;

    private int $scheduleId;

    private int $startTime;

    private int $duration;

    public function __construct(int $id, int $scheduleId, int $startTime, int $duration)
    {
        $this->id = $id;
        $this->scheduleId = $scheduleId;
        $this->startTime = $startTime;
        $this->duration = $duration;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getScheduleId(): int
    {
        return $this->scheduleId;
    }

    public function getStartTime(): int
    {
        return $this->startTime;
    }

    public function getDuration(): int
    {
        return $this->duration;
    }

    public function toArray(): array
    {
        return [
            "id" => $this->getId(),
            "schedule_id" => $this->getScheduleId(),
            "start_time" => $this->getStartTime(),
            "duration" => $this->getDuration(),
        ];
    }
}