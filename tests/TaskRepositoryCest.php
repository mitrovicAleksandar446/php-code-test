<?php
declare(strict_types=1);

namespace Tests;

use Codeception\Example;
use Exception;
use Mockery\MockInterface;
use Tymeshift\PhpTest\Components\DatabaseInterface;
use Tymeshift\PhpTest\Components\HttpClientInterface;
use Tymeshift\PhpTest\Domains\Task\TaskCollection;
use Tymeshift\PhpTest\Domains\Task\TaskEntity;
use Tymeshift\PhpTest\Domains\Task\TaskFactory;
use Tymeshift\PhpTest\Domains\Task\TaskRepository;
use Tymeshift\PhpTest\Domains\Task\TaskStorage;
use Tymeshift\PhpTest\Exceptions\InvalidCollectionDataProvidedException;
use Tymeshift\PhpTest\Exceptions\StorageDataMissingException;
use UnitTester;

class TaskCest
{
    /**
     * @var TaskRepository|null
     */
    private ?TaskRepository $taskRepository;

    private MockInterface $httpClientMock;

    private MockInterface $dbInterfaceMock;

    public function _before()
    {
        $this->httpClientMock = \Mockery::mock(HttpClientInterface::class);
        $this->dbInterfaceMock = \Mockery::mock(DatabaseInterface::class);
        $storage = new TaskStorage($this->httpClientMock, $this->dbInterfaceMock);
        $this->taskRepository = new TaskRepository($storage, new TaskFactory());
    }

    public function _after()
    {
        $this->taskRepository = null;
        \Mockery::close();
    }

    /**
     * @test
     * @dataProvider _tasksDataProvider
     * @param Example $example
     * @param UnitTester $tester
     * @throws InvalidCollectionDataProvidedException
     */
    public function getTasksByScheduleIdIsReturningTaskCollection(Example $example, UnitTester $tester)
    {
        [$task1, $task2, $task3] = $example;
        $scheduleId = 1;
        $this->httpClientMock->allows()
            ->request('GET', "https://my-api/api/v1/schedules/{$scheduleId}/tasks")
            ->andReturns($this->_tasksDataProvider()[0]);

        $tasks = $this->taskRepository->getByScheduleId($scheduleId);
        $tasksArray = $tasks->toArray();
        $tester->assertInstanceOf(TaskCollection::class, $tasks);
        $tester->assertEquals([$task1, $task2, $task3], $tasksArray);
    }

    /**
     * @test
     * @param UnitTester $tester
     */
    public function getTaskByIdReturnsEntity(UnitTester $tester)
    {
        $taskId = 2;
        $this->dbInterfaceMock->allows()
            ->query('SELECT * FROM tasks WHERE id=:id LIMIT 1', ["id" => $taskId])
            ->andReturns(['id' => $taskId, 'schedule_id' => 12, 'start_time' => 0, 'duration' => 0]);

        $task = $this->taskRepository->getById($taskId);
        $tester->assertInstanceOf(TaskEntity::class, $task);
    }

    /**
     * @test
     * @param UnitTester $tester
     */
    public function getTasksByIdsFailsWhenInvalidReturnProvided(UnitTester $tester)
    {
        $taskIds = [1,2];
        $this->dbInterfaceMock->shouldReceive('query')
            ->with('SELECT * FROM tasks WHERE id in (:ids)', ["ids" => $taskIds])
            ->andReturns(['invalid']);

        $tester->expectThrowable(InvalidCollectionDataProvidedException::class, function () use ($taskIds) {
            $this->taskRepository->getByIds($taskIds);
        });
    }

    /**
     * @test
     * @param UnitTester $tester
     */
    public function getTasksByIdFailsWhenStorageReturnIsEmpty(UnitTester $tester)
    {
        $taskId = 1;
        $this->dbInterfaceMock->shouldReceive('query')
            ->with('SELECT * FROM tasks WHERE id=:id LIMIT 1', ["id" => $taskId])
            ->andReturns([]);

        $tester->expectThrowable(StorageDataMissingException::class, function () use ($taskId) {
            $this->taskRepository->getById($taskId);
        });
    }

    public function _tasksDataProvider()
    {
        return [
            [
                ["id" => 123, "schedule_id" => 1, "start_time" => 0, "duration" => 3600],
                ["id" => 431, "schedule_id" => 1, "start_time" => 3600, "duration" => 650],
                ["id" => 332, "schedule_id" => 1, "start_time" => 5600, "duration" => 3600],
            ]
        ];
    }
}