<?php

namespace TaskTracker\Infra\Storage;

use TaskTracker\Infra\Storage\Enum\StorageType;

/**
 * Persists the user's chosen storage type so the preference survives across
 * separate command invocations. Stored as a small text file in the data path.
 */
class StorageConfig
{
    private const string CONFIG_FILE = '.storage-type';
    private const StorageType DEFAULT_TYPE = StorageType::JSON;

    private string $configPath;

    public function __construct(string $path)
    {
        $this->configPath = $path . DIRECTORY_SEPARATOR . self::CONFIG_FILE;
    }

    public function get(): StorageType
    {
        if (!file_exists($this->configPath)) {
            return self::DEFAULT_TYPE;
        }

        $value = trim((string)file_get_contents($this->configPath));

        return StorageType::tryFrom($value) ?? self::DEFAULT_TYPE;
    }

    public function set(StorageType $type): void
    {
        $dir = dirname($this->configPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        file_put_contents($this->configPath, $type->value);
    }
}
