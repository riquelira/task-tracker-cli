<?php

namespace TaskTracker\Infra\Mapper;

use DateTimeImmutable;
use TaskTracker\Domain\Entity\Enum\TaskStatus;
use TaskTracker\Domain\Entity\Task;

class TaskMapper
{
    /**
     * @throws \DateMalformedStringException
     */
    public function arrayToTask(array $data): Task
    {
        $task = new Task(
            $data['title'],
            $data['description'],
            TaskStatus::from($data['status']),
        );

        if (isset($data['id'])) {
            $task->assignId($data['id']);
        }

        if (!empty($data['createdAt'])) {
            $task->createdAt = new DateTimeImmutable($data['createdAt']);
        }

        if (!empty($data['updatedAt'])) {
            $task->updatedAt = new DateTimeImmutable($data['updatedAt']);
        }

        return $task;
    }

    public function mapToTasks(array $tasks): array
    {
        return array_map(fn($task) => $this->arrayToTask($task), $tasks);
    }

    public function toArray(Task $task): array
    {
        return [
            'id'            => $task->id,
            'title'         => $task->title,
            'description'   => $task->description,
            'status'        => $task->status->value,
            'createdAt'     => $task->createdAt?->format('Y-m-d H:i:s'),
            'updatedAt'     => $task->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}