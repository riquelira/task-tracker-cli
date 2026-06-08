<?php

namespace TaskTracker\Infra\Repository;

use TaskTracker\Domain\Entity\Task;
use TaskTracker\Infra\Mapper\TaskMapper;
use TaskTracker\Infra\Storage\FileStorage;

class TaskRepository
{
    private FileStorage $storage;
    private TaskMapper $mapper;

    /**
     * @param FileStorage $storage
     */
    public function __construct(FileStorage $storage)
    {
        $this->storage = $storage;
        $this->mapper = new TaskMapper();
    }

    /**
     * @param Task $task
     * @return Task
     */
    public function save(Task $task): Task
    {
        $data = $this->mapper->toArray($task);

        if ($task->id === null) {
            $generatedId = $this->storage->insert($data);
            $task->assignId($generatedId);

            return $task;
        }

        $tasks = $this->storage->load();
        $taskIndex = $this->findTaskIndexById($task->id, $tasks);

        $tasks[$taskIndex] = $data;
        $this->storage->persist($tasks);

        return $task;
    }

    /**
     * @param int $id
     * @return void
     */
    public function delete(int $id): void
    {
        $tasks = $this->storage->load();
        $taskIndex = $this->findTaskIndexById($id, $tasks);

        if ($taskIndex === null) {
            return;
        }

        $this->storage->remove($id);
    }

    /**
     * @param int $id
     * @return Task|null
     */
    public function findById(int $id): ?Task
    {
        $tasks = $this->storage->load();
        $taskIndex = $this->findTaskIndexById($id, $tasks);

        if ($taskIndex === null) {
            return null;
        }

        return $this->mapper->arrayToTask($tasks[$taskIndex]);
    }


    /**
     * @return Task[]
     */
    public function findAll(): array
    {
        $tasks = $this->storage->load();
        return $this->mapper->mapToTasks($tasks);
    }

    /**
     * @param string $key
     * @param string $value
     * @return Task[]
     */
    public function findAllBy(string $key, string $value): array
    {
        $tasks = $this->storage->load();

        $pattern = "/$value/i";
        $tasksFound = array_filter($tasks, fn($task) => preg_match($pattern, $task[$key] ?? ''));

        return $this->mapper->mapToTasks($tasksFound);
    }

    /**
     * @param int $id
     * @param array $tasks
     * @return int|null
     */
    public function findTaskIndexById(int $id, array $tasks): ?int
    {
        $taskIds = array_map('intval', array_column($tasks, 'id'));
        $index = array_search($id, $taskIds, true);

        if ($index === false) {
            return null;
        };

        return $index;
    }
}