<?php

namespace Tymeshift\PhpTest\Domains\Schedule;

use Tymeshift\PhpTest\Exceptions\StorageDataMissingException;
use Tymeshift\PhpTest\Interfaces\EntityInterface;
use Tymeshift\PhpTest\Interfaces\FactoryInterface;

class ScheduleRepository
{
    private ScheduleStorage $storage;

    private FactoryInterface $factory;

    public function __construct(ScheduleStorage $storage, FactoryInterface $factory)
    {
        $this->storage = $storage;
        $this->factory = $factory;
    }

    /**
     * @param int $id
     * @return ScheduleEntity|EntityInterface
     * @throws StorageDataMissingException
     */
    public function getById(int $id):EntityInterface|ScheduleEntity
    {
        $data = $this->storage->getById($id);
        if (!$data) {
            throw new StorageDataMissingException('Schedule is missing');
        }
        return $this->factory->createEntity($data);
    }
}