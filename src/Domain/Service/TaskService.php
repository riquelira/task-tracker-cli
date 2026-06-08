<?php

namespace TaskTracker\Domain\Service;

use DateTimeImmutable;
use TaskTracker\Domain\Entity\Enum\TaskStatus;
use TaskTracker\Domain\Entity\Task;
use TaskTracker\Infra\Mapper\TaskMapper;
use TaskTracker\Infra\Repository\TaskRepository;
use TaskTracker\Infra\Storage\FileStorage;

class TaskService
{
    private TaskRepository $repository;
    private TaskMapper $mapper;

    /**
     * @param FileStorage $storage
     */
    public function __construct(FileStorage $storage)
    {
        $this->repository = new TaskRepository($storage);
        $this->mapper = new TaskMapper();
    }

    /**
     * @param string $title
     * @param string $description
     * @return bool
     * @throws \DateMalformedStringException
     */
    public function createTask(string $title, string $description): bool
    {
        $task = $this->mapper->arrayToTask([
            'title' => $title,
            'description' => $description,
            'status' => TaskStatus::TODO->value,
        ]);

        return (bool) $this->repository->save($task);
    }

    /**
     * @param int $id
     * @param string $property
     * @param string $value
     * @return bool
     */
    public function updateTask(int $id, string $property, string $value): bool
    {
        $this->isUpdatable($property, $value);

        $task = $this->getTaskById($id);
        if ($task === null)
            throw new \InvalidArgumentException("Task with id {$id} not found");

        $task->$property = $value;
        $task->updatedAt = new DateTimeImmutable();

        return (bool) $this->repository->save($task);
    }

    /**
     * @param string $property
     * @param mixed $value
     * @return bool
     */
    public function updateAllTasks(string $property, mixed $value): bool
    {
        $this->isUpdatable($property, $value);

        $tasks = $this->getAllTasks();

        foreach ($tasks as $task) {
            $task->$property = $value;
            $task->updatedAt = new DateTimeImmutable();

            $this->repository->save($task);
        }

        return true;
    }

    /**
     * @param int $id
     * @return bool
     */
    public function deleteTask(int $id): bool
    {
        $task = $this->getTaskById($id);
        if ($task === null)
            throw new \InvalidArgumentException("Task with id {$id} not found");

        $this->repository->delete($task->id);

        return !(bool) $this->getTaskById($id);
    }

    /**
     * @return bool
     */
    public function deleteAllTasks(): bool
    {
        $tasks = $this->getAllTasks();

        foreach ($tasks as $task) {
            $this->repository->delete($task->id);
        }

        return count($this->getAllTasks()) === 0;
    }

    /**
     * @param int $id
     * @return Task|null
     */
    public function getTaskById(int $id): ?Task
    {
        return $this->repository->findById($id);
    }

    /**
     * @param string|null $key
     * @param string|null $value
     * @return Task[]
     */
    public function getAllTasks(?string $key = null, ?string $value = null): array
    {
        if ($key && $value) {
            if ($key === 'status') {
                $value = match (strtolower($value)) {
                    'todo' => TaskStatus::TODO->value,
                    'in-progress' => TaskStatus::IN_PROGRESS->value,
                    'done' => TaskStatus::DONE->value,
                    default => throw new \InvalidArgumentException("Invalid status value '{$value}'. Allowed values are: 'todo', 'in-progress', 'done'"),
                };
            }
            return $this->repository->findAllBy($key, $value);
        }

        return $this->repository->findAll();
    }

    /**
     * @param string $property
     * @param string $value
     * @return void
     */
    public function isUpdatable(string $property, string &$value): void
    {
        $updatable = ['title', 'description', 'status'];
        if (!in_array($property, $updatable, true)) {
            throw new \InvalidArgumentException("Property '{$property}' is not updatable");
        }

        if ($property === 'status') {
            $updatableStatus = ['todo', 'in-progress', 'done'];
            if (!in_array(strtolower($value), $updatableStatus, true)) {
                throw new \InvalidArgumentException("Invalid status value '{$value}'. Allowed values are: " . implode(', ', $updatableStatus));
            }

            $value = match (strtolower($value)) {
                'todo' => TaskStatus::TODO,
                'in-progress' => TaskStatus::IN_PROGRESS,
                'done' => TaskStatus::DONE,
            };
        }
    }
}