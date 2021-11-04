<?php
declare(strict_types=1);

namespace Tymeshift\PhpTest\Domains\Task;

use Tymeshift\PhpTest\Components\DatabaseInterface;
use Tymeshift\PhpTest\Components\HttpClientInterface;

class TaskStorage
{
    // in absence of some config or env file
    private const SCHEDULE_URL = 'https://my-api/api/v1/schedules/{id}/tasks';

    private HttpClientInterface $client;

    private DatabaseInterface $db;

    public function __construct(HttpClientInterface $httpClient, DatabaseInterface $db)
    {
        $this->client = $httpClient;
        $this->db = $db;
    }

    public function getById(int $id): array
    {
        return $this->db->query('SELECT * FROM tasks WHERE id=:id LIMIT 1', ["id" => $id]);
    }

    public function getByScheduleId(int $scheduleId): array
    {
        $url = str_replace('{id}', (string)$scheduleId, static::SCHEDULE_URL);
        return $this->client->request('GET', $url);
    }

    /**
     * @param array<int> $ids
     * @return array
     */
    public function getByIds(array $ids): array
    {
        return $this->db->query('SELECT * FROM tasks WHERE id in (:ids)', ["ids" => $ids]);
    }
}