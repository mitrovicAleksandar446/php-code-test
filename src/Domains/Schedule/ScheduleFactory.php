<?php

namespace Tymeshift\PhpTest\Domains\Schedule;

use Tymeshift\PhpTest\Interfaces\EntityInterface;
use Tymeshift\PhpTest\Interfaces\FactoryInterface;

class ScheduleFactory implements FactoryInterface
{

    public function createEntity(array $data): EntityInterface
    {
        $entity = new ScheduleEntity();
        if (isset($data['id']) && is_int($data['id'])) {
            $entity->setId($data['id']);
        }

        if (isset($data['start_time']) && is_int($data['start_time'])) {
            $entity->setStartTime((new \DateTime())->setTimestamp($data['start_time']));
        }

        if (isset($data['end_time']) && is_int($data['end_time'])) {
            $entity->setEndTime((new \DateTime())->setTimestamp($data['end_time']));
        }

        if (isset($data['name']) && is_string($data['name'])) {
            $entity->setName($data['name']);
        }

        return $entity;
    }
}