<?php
declare(strict_types=1);

namespace Tymeshift\PhpTest\Domains\Task;

use Tymeshift\PhpTest\Exceptions\InvalidCollectionDataProvidedException;
use Tymeshift\PhpTest\Exceptions\StorageDataMissingException;
use Tymeshift\PhpTest\Interfaces\EntityInterface;
use Tymeshift\PhpTest\Interfaces\RepositoryInterface;

class TaskRepository implements RepositoryInterface
{
    /**
     * @var TaskFactory
     */
    private TaskFactory $factory;

    /**
     * @var TaskStorage
     */
    private TaskStorage $storage;

    public function __construct(TaskStorage $storage, TaskFactory $factory)
    {
        $this->factory = $factory;
        $this->storage = $storage;
    }

    /**
     * @param int $id
     * @return EntityInterface
     * @throws StorageDataMissingException
     */
    public function getById(int $id): EntityInterface
    {
        $rawTask = $this->storage->getById($id);
        if (!$rawTask) {
            throw new StorageDataMissingException('Task is missing');
        }
        return $this->factory->createEntity($rawTask);
    }

    /**
     * @param int $scheduleId
     * @return TaskCollection
     * @throws InvalidCollectionDataProvidedException
     */
    public function getByScheduleId(int $scheduleId):TaskCollection
    {
        $rawTasks = $this->storage->getByScheduleId($scheduleId);
        return $this->factory->createCollection($rawTasks);
    }

    /**
     * @param array $ids
     * @return TaskCollection
     * @throws InvalidCollectionDataProvidedException
     */
    public function getByIds(array $ids): TaskCollection
    {
        $rawTasks = $this->storage->getByIds($ids);
        return $this->factory->createCollection($rawTasks);
    }
}