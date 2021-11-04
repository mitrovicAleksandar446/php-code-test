<?php
declare(strict_types=1);
namespace Tests;

use Codeception\Example;
use Mockery\MockInterface;
use Tymeshift\PhpTest\Domains\Schedule\ScheduleRepository;
use Tymeshift\PhpTest\Domains\Schedule\ScheduleFactory;
use Tymeshift\PhpTest\Domains\Schedule\ScheduleStorage;
use Tymeshift\PhpTest\Exceptions\StorageDataMissingException;
use UnitTester;

class ScheduleRepositoryCest
{
    private ?MockInterface $scheduleStorageMock;

    private ?ScheduleRepository $scheduleRepository;

    public function _before()
    {
        $this->scheduleStorageMock = \Mockery::mock(ScheduleStorage::class);
        $this->scheduleRepository = new ScheduleRepository($this->scheduleStorageMock, new ScheduleFactory());
    }

    public function _after()
    {
        $this->scheduleRepository = null;
        $this->scheduleStorageMock = null;
        \Mockery::close();
    }

    /**
     * @test
     * @dataProvider _scheduleProvider
     * @param Example $example
     * @param UnitTester $tester
     * @throws StorageDataMissingException
     */
    public function getByIdRepositoryReturnsScheduleEntity(Example $example, UnitTester $tester)
    {
        ['id' => $id, 'start_time' => $startTime, 'end_time' => $endTime, 'name' => $name] = $example;

        $this->scheduleStorageMock
            ->shouldReceive('getById')
            ->with($id)
            ->andReturn(['id' => $id, 'start_time' => $startTime, 'end_time' => $endTime, 'name' => $name]);

        $entity = $this->scheduleRepository->getById($id);
        $tester->assertEquals($id, $entity->getId());
        $tester->assertEquals($startTime, $entity->getStartTime()->getTimestamp());
        $tester->assertEquals($endTime, $entity->getEndTime()->getTimestamp());
    }

    /**
     * @test
     * @param UnitTester $tester
     */
    public function getByIdFailsWhenStorageReturnsEmptyPayload(UnitTester $tester)
    {
        $this->scheduleStorageMock
            ->shouldReceive('getById')
            ->with(4)
            ->andReturn([]);

        $tester->expectThrowable(StorageDataMissingException::class, function () {
            $this->scheduleRepository->getById(4);
        });
    }

    /**
     * @return array[]
     */
    protected function _scheduleProvider()
    {
        return [
            ['id' => 1, 'start_time' => 1631232000, 'end_time' => 1631232000 + 86400, 'name' => 'Test'],
        ];
    }
}