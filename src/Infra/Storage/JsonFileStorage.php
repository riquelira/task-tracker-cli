<?php

namespace TaskTracker\Infra\Storage;

use TaskTracker\Infra\Storage\Enum\StorageType;

class JsonFileStorage extends FileStorage
{

    protected function storageType(): StorageType
    {
        return StorageType::JSON;
    }

    public function persist(array $data): void
    {
        $json = json_encode(
            $data,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
        );

        $this->writeStorage($json);

    }

    public function load(): array
    {
        $dataStorage = $this->readStorage();
        return json_decode($dataStorage, true) ?? [];
    }
}