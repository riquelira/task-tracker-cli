<?php

namespace TaskTracker\Infra\Storage;

use TaskTracker\Infra\Storage\Enum\StorageType;

class YamlFileStorage extends FileStorage
{

    protected function storageType(): StorageType
    {
        return StorageType::YAML;
    }

    public function persist(array $data): void
    {
        $this->writeStorage(yaml_emit($data, YAML_UTF8_ENCODING));
    }

    public function load(): array
    {
        return yaml_parse_file($this->storagePath) ?? [];
    }
}