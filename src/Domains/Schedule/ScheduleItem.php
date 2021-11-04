<?php


namespace Tymeshift\PhpTest\Domains\Schedule;


class ScheduleItem implements ScheduleItemInterface
{
    private int $scheduleId;

    private int $startTime;

    private int $endTime;

    private string $type;

    public function __construct(int $scheduleId, int $startTime, int $endTime, string $type)
    {
        $this->scheduleId = $scheduleId;
        $this->startTime = $startTime;
        $this->endTime = $endTime;
        $this->type = $type;
    }

    public function getScheduleId(): int
    {
        return $this->scheduleId;
    }

    public function getStartTime(): int
    {
        return $this->startTime;
    }

    public function getEndTime(): int
    {
        return $this->endTime;
    }

    public function getType(): string
    {
        return $this->type;
    }
}