<?php

namespace TaskTracker\Infra\Storage;

use TaskTracker\Infra\Storage\Enum\StorageType;

class StorageFactory
{
    public static function create(StorageType $type, string $path): FileStorage
    {
        return match ($type) {
            StorageType::JSON => new JsonFileStorage($path),
            StorageType::YAML => new YamlFileStorage($path),
            StorageType::XML => new XmlFileStorage($path, 'tasks', 'task'),
            StorageType::CSV => new CsvFileStorage($path),
        };
    }
}
