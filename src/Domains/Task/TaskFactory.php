<?php
declare(strict_types=1);

namespace Tymeshift\PhpTest\Domains\Task;

use Tymeshift\PhpTest\Domains\Schedule\ScheduleEntity;
use Tymeshift\PhpTest\Exceptions\InvalidCollectionDataProvidedException;
use Tymeshift\PhpTest\Interfaces\CollectionInterface;
use Tymeshift\PhpTest\Interfaces\EntityInterface;
use Tymeshift\PhpTest\Interfaces\FactoryInterface;

class TaskFactory implements FactoryInterface
{

    public function createEntity(array $data): EntityInterface|TaskEntity
    {
        return new TaskEntity(
            $data['id'],
            $data['schedule_id'],
            $data['start_time'],
            $data['duration'],
        );
    }

    /**
     * @param array $data
     * @return TaskCollection|CollectionInterface
     * @throws InvalidCollectionDataProvidedException
     */
    public function createCollection(array $data):CollectionInterface|TaskCollection
    {
        return (new TaskCollection())->createFromArray($data, $this);
    }
}