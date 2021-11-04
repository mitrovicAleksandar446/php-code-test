<?php


namespace Tymeshift\PhpTest\Domains\Schedule;

use Tymeshift\PhpTest\Domains\Task\TaskCollection;
use Tymeshift\PhpTest\Domains\Task\TaskEntity;
use Tymeshift\PhpTest\Domains\Task\TaskRepository;
use Tymeshift\PhpTest\Exceptions\InvalidCollectionDataProvidedException;
use Tymeshift\PhpTest\Exceptions\StorageDataMissingException;

class ScheduleService
{
    private ScheduleRepository $scheduleRepository;
    private TaskRepository $taskRepository;

    public function __construct(ScheduleRepository $scheduleRepository, TaskRepository $taskRepository)
    {
        $this->scheduleRepository = $scheduleRepository;
        $this->taskRepository = $taskRepository;
    }

    /**
     * @param int $scheduleId
     * @return ScheduleEntity
     * @throws InvalidCollectionDataProvidedException
     * @throws StorageDataMissingException
     */
    public function getScheduleById(int $scheduleId): ScheduleEntity
    {
        $schedule = $this->scheduleRepository->getById($scheduleId);
        $tasks = $this->taskRepository->getByScheduleId($scheduleId);
        $scheduleItems = $this->hydrateTasksToScheduleItems($tasks);

        $schedule->setItems($scheduleItems);

        return $schedule;
    }

    /**
     * @param TaskCollection<TaskEntity> $taskCollection
     * @return array<ScheduleItemInterface>
     */
    private function hydrateTasksToScheduleItems(TaskCollection $taskCollection): array
    {
        $scheduleItems = [];
        foreach ($taskCollection as $task) {
            $scheduleItems []= new ScheduleItem(
                $task->getScheduleId(),
                $task->getStartTime(),
                $task->getStartTime() + $task->getDuration(),
                $task::TYPE
            );
        }
        return $scheduleItems;
    }
}