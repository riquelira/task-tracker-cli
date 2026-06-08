<?php

namespace TaskTracker\Domain\Entity;

use DateTimeImmutable;
use TaskTracker\Domain\Entity\Enum\TaskStatus;

final class Task
{
    private(set) ?int $id = null {
        get {
            return $this->id;
        }
    }

    public string $title {
        get {
            return $this->title;
        }
        set {
            $this->title = $value;
        }
    }

    public string $description {
        get {
            return $this->description;
        }
        set {
            $this->description = $value;
        }
    }

    public TaskStatus $status {
        get {
            return $this->status;
        }
        set {
            $this->status = $value;
        }
    }

    public ?DateTimeImmutable $createdAt {
        get {
            return $this->createdAt;
        }
        set {
            $this->createdAt = $value;
        }
    }

    public ?DateTimeImmutable $updatedAt = null {
        get {
            return $this->updatedAt;
        }
        set {
            $this->updatedAt = $value;
        }
    }

    public function __construct(string $title, string $description, TaskStatus $status)
    {
        $this->title = $title;
        $this->description = $description;
        $this->status = $status;
        $this->createdAt = new DateTimeImmutable();
        $this->validate();
    }

    public function __toString(): string
    {
        return <<<EOL
        Id: {$this?->id}
        Title: {$this?->title}
        Description: {$this?->description}
        Status: {$this?->status->name}
        Created At: {$this?->createdAt?->format('Y-m-d H:i:s')}
        Updated At: {$this?->updatedAt?->format('Y-m-d H:i:s')} 
        EOL;
    }

    private function validate(): void
    {
        if (empty($this->title)) {
            throw new \InvalidArgumentException('Title cannot be empty');
        }

        if (empty($this->description)) {
            throw new \InvalidArgumentException('Description cannot be empty');
        }

        if (empty($this->status)) {
            throw new \InvalidArgumentException('Status cannot be empty');
        }

        if (empty($this->createdAt)) {
            throw new \InvalidArgumentException('CreatedAt cannot be empty');
        }
    }

    public function assignId(int $id): void
    {
        if ($this->id !== null) {
            throw new \LogicException('Id is already assigned');
        }

        $this->id = $id;
    }
}