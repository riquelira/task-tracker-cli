<?php

namespace TaskTracker\Infra\Storage;

interface StorageInterface
{
    public function persist(array $data): void;

    public function insert(array $data): int;

    public function remove(int $id): void;

    public function load(): array;
}