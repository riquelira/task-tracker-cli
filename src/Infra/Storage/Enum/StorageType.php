<?php

namespace TaskTracker\Infra\Storage\Enum;

enum StorageType: string
{
    case JSON = 'json';
    case YAML = 'yaml';
    case XML = 'xml';
    case CSV = 'csv';

    /**
     * @return string[] Valid storage type names (e.g. ['json', 'yaml', 'xml', 'csv'])
     */
    public static function names(): array
    {
        return array_map(fn(self $type) => $type->value, self::cases());
    }
}
