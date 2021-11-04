<?php
declare(strict_types=1);

namespace Tests;

use Mockery\MockInterface;
use Tymeshift\PhpTest\Domains\Schedule\ScheduleEntity;
use Tymeshift\PhpTest\Domains\Schedule\ScheduleFactory;
use Tymeshift\PhpTest\Domains\Schedule\ScheduleItemInterface;
use Tymeshift\PhpTest\Domains\Schedule\ScheduleRepository;
use Tymeshift\PhpTest\Domains\Schedule\ScheduleService;
use Tymeshift\PhpTest\Domains\Task\TaskCollection;
use Tymeshift\PhpTest\Domains\Task\TaskFactory;
use Tymeshift\PhpTest\Domains\Task\TaskRepository;
use Tymeshift\PhpTest\Exceptions\InvalidCollectionDataProvidedException;
use Tymeshift\PhpTest\Exceptions\StorageDataMissingException;
use UnitTester;

class ScheduleServiceCest
{

    private ?MockInterface $scheduleRepository;

    private ?MockInterface $taskRepository;

    private ScheduleService $scheduleService;

    public function _before()
    {
        $this->scheduleRepository = \Mockery::mock(ScheduleRepository::class);
        $this->taskRepository = \Mockery::mock(TaskRepository::class);

        $this->scheduleService = new ScheduleService($this->scheduleRepository, $this->taskRepository);
    }

    public function _after()
    {
        $this->scheduleRepository = null;
        $this->taskRepository = null;
        \Mockery::close();
    }

    /**
     * @test
     * @param UnitTester $tester
     */
    public function throwsInvalidCollectionExceptionWhenRepositoryIsThrowingException(UnitTester $tester)
    {
        $this->scheduleRepository->expects('getById')->andThrow(InvalidCollectionDataProvidedException::class);
        $tester->expectThrowable(InvalidCollectionDataProvidedException::class, function () {
            $this->scheduleService->getScheduleById(1);
        });
    }

    /**
     * @test
     * @param UnitTester $tester
     * @throws InvalidCollectionDataProvidedException|StorageDataMissingException
     */
    public function returnsEmptyScheduledItemsWhenThereAreNoTasks(UnitTester $tester)
    {
        $scheduleFactory = new ScheduleFactory();
        $scheduleEntity = $scheduleFactory->createEntity([
            "id" => 1,
            "start_time" => 12,
            "end_time" => 13,
            "name" => "test"
        ]);
        $this->scheduleRepository->expects('getById')
            ->once()
            ->andReturns($scheduleEntity);

        $this->taskRepository->expects('getByScheduleId')
            ->once()
            ->andReturns(new TaskCollection([]));

        $scheduleEntity = $this->scheduleService->getScheduleById(1);
        $tester->assertEquals($scheduleEntity->getItems(), []);
        $tester->assertEquals($scheduleEntity->getId(), 1);
        $tester->assertEquals($scheduleEntity->getName(), 'test');
        $tester->assertEquals($scheduleEntity->getStartTime()->getTimestamp(), 12);
        $tester->assertEquals($scheduleEntity->getEndTime()->getTimestamp(), 13);
    }

    /**
     * @test
     * @param UnitTester $tester
     * @throws InvalidCollectionDataProvidedException|StorageDataMissingException
     */
    public function returnsHydratedScheduleItemsWhenThereAreTasks(UnitTester $tester)
    {
        $scheduleFactory = new ScheduleFactory();
        $taskFactory = new TaskFactory();

        $scheduleEntity = $scheduleFactory->createEntity([
            "id" => 1,
            "start_time" => 12,
            "end_time" => 13,
            "name" => "test"
        ]);

        $taskEntity = $taskFactory->createEntity([
            "id" => 1,
            "schedule_id" => 12,
            "start_time" => 13,
            "duration" => 56
        ]);
        $taskCollection = new TaskCollection([$taskEntity]);
        $this->scheduleRepository->expects('getById')
            ->once()
            ->andReturns($scheduleEntity);
        $this->taskRepository->expects('getByScheduleId')
            ->once()
            ->andReturns($taskCollection);

        $scheduleEntity = $this->scheduleService->getScheduleById(1);
        $tester->assertEquals(count($scheduleEntity->getItems()), count($taskCollection));
        $tester->assertInstanceOf(ScheduleItemInterface::class, $scheduleEntity->getItems()[0]);
        $tester->assertEquals($scheduleEntity->getId(), 1);
        $tester->assertEquals($scheduleEntity->getName(), 'test');
        $tester->assertEquals($scheduleEntity->getStartTime()->getTimestamp(), 12);
        $tester->assertEquals($scheduleEntity->getEndTime()->getTimestamp(), 13);
    }
}