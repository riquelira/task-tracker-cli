<?php

namespace TaskTracker\Infra\Storage;

use TaskTracker\Infra\Storage\Enum\StorageType;

class CsvFileStorage extends FileStorage
{

    protected function storageType(): StorageType
    {
        return StorageType::CSV;
    }

    public function persist(array $data): void
    {
        $writeHandle = $this->getStorageWriteHandle();

        if ($data === []) {
            // Lista vazia: trunca o arquivo e não escreve cabeçalho.
            return;
        }

        $headers = array_keys($data[0]);

        fputcsv($writeHandle, $headers);

        foreach ($data as $row) {
            fputcsv($writeHandle, $row);
        }
    }

    public function load(): array
    {
        $readHandle = $this->getStorageReadHandle();

        $headers = fgetcsv($readHandle); // read first iterator

        $data = [];
        while (($row = fgetcsv($readHandle)) !== false) {
            $data[] = array_combine($headers, $row);
        }

        return $data;
    }
}