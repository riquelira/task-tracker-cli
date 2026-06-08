<?php

namespace TaskTracker\Infra\Storage;

use TaskTracker\Infra\Storage\Enum\StorageType;

abstract class FileStorage implements StorageInterface
{
    protected string $storageName = 'my-tasks.store';
    protected string $storagePath;

    public function __construct(string $path)
    {
        $this->storagePath = $path
            . DIRECTORY_SEPARATOR
            . $this->storageName
            . '.'
            . strtolower($this->storageType()->name);

        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }

        if (!file_exists($this->storagePath)) {
            touch($this->storagePath);
        }
    }

    // Abstract function because PHP doesn't have abstract property,
    // so we need to define the storage type in the child class.
    abstract protected function storageType(): StorageType;

    protected function writeStorage(string $data): void
    {
        file_put_contents($this->storagePath, $data);
    }

    protected function readStorage(): string
    {
        return file_get_contents($this->storagePath);
    }

    protected function getStorageWriteHandle()
    {
        return fopen($this->storagePath, 'w');
    }

    protected function getStorageReadHandle()
    {
        return fopen($this->storagePath, 'r');
    }

    public function insert(array $data): int
    {
        $dataStorage = $this->load();

        $newId = $this->generateNewId($dataStorage);
        $data['id'] = $newId;

        $dataStorage[] = $data;
        $this->persist($dataStorage);

        return $newId;
    }

    public function remove(int $id): void
    {
        $dataStorage = $this->load();

        $dataIndex = $this->findIndexById($id, $dataStorage);

        if ($dataIndex === null) {
            return;
        }

        array_splice($dataStorage, $dataIndex, 1);

        $this->persist($dataStorage);
    }

    protected function findIndexById(int $id, array $data): ?int
    {
        $taskIds = array_map('intval', array_column($data, 'id'));
        $index = array_search($id, $taskIds, true);

        if ($index === false) {
            return null;
        };

        return $index;
    }

    protected function generateNewId(array $data): int
    {
        return (end($data)['id'] ?? 0) + 1;
    }
}