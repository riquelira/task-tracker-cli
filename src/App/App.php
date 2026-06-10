<?php

namespace TaskTracker\App;

use TaskTracker\Cli\Cli;
use TaskTracker\Cli\OutputFormatter;
use TaskTracker\Domain\Service\TaskService;
use TaskTracker\Infra\Storage\Enum\StorageType;
use TaskTracker\Infra\Storage\FileStorage;
use TaskTracker\Infra\Storage\StorageConfig;

class App
{
    private TaskService $taskService;
    private OutputFormatter $formatter;
    private StorageConfig $storageConfig;

    public function __construct(FileStorage $storage, StorageConfig $storageConfig)
    {
        date_default_timezone_set($this->getUserTimezone());

        $this->taskService = new TaskService($storage);
        $this->formatter = new OutputFormatter();
        $this->storageConfig = $storageConfig;
    }

    public function run(array $args): string
    {
        $cli = new Cli($args);

        return match ($cli->action) {
            'help' => $cli->help(),

            'storage' => $this->setStorageType(...$cli->actionArgs),

            'add' => $this->taskService->createTask(...$cli->actionArgs)
                ? 'Task created successfully.' : 'Error creating task.',

            'update' => $this->taskService->updateTask(...$cli->actionArgs)
                ? 'Task updated successfully.' : 'Error updating task.',

            'update-all' => $this->taskService->updateAllTasks(...$cli->actionArgs)
                ? 'All tasks updated successfully.' : 'Error updating all tasks.',

            'delete' => $this->taskService->deleteTask(...$cli->actionArgs)
                ? 'Task deleted successfully.' : 'Error deleting task.',

            'delete-all' => $this->taskService->deleteAllTasks()
                ? 'All tasks deleted successfully.' : 'Error deleting all tasks.',

            'list' => $this->formatter->task($this->taskService->getTaskById(...$cli->actionArgs)),

            'list-all' => $this->formatter->tasks($this->taskService->getAllTasks(...$cli->actionArgs)),
        };
    }

    private function setStorageType(string $type): string
    {
        // The type is already validated by the Cli guard rails, so from() is safe.
        $storageType = StorageType::from(strtolower($type));
        $this->storageConfig->set($storageType);

        return "Storage type set to '{$storageType->value}'"
            . PHP_EOL . "Note: tasks stored in other formats are not migrated automatically.";
    }

    private function getUserTimezone(): string
    {
        // Debian-based systems store the IANA timezone name directly in /etc/timezone.
        if (is_readable('/etc/timezone')) {
            $timezone = trim((string) file_get_contents('/etc/timezone'));

            if ($this->isValidTimezone($timezone)) {
                return $timezone;
            }
        }

        // RedHat-based (and modern systemd) systems symlink /etc/localtime into
        // the zoneinfo database, e.g. /usr/share/zoneinfo/America/Sao_Paulo.
        if (is_link('/etc/localtime')) {
            $target = (string) readlink('/etc/localtime');

            if (preg_match('#/zoneinfo/(.+)$#', $target, $matches)) {
                $timezone = $matches[1];

                if ($this->isValidTimezone($timezone)) {
                    return $timezone;
                }
            }
        }

        // Older RedHat releases keep the zone in /etc/sysconfig/clock (ZONE="...").
        if (is_readable('/etc/sysconfig/clock')) {
            $contents = (string) file_get_contents('/etc/sysconfig/clock');

            if (preg_match('/^\s*ZONE\s*=\s*"?([^"\n]+)"?/m', $contents, $matches)) {
                $timezone = trim($matches[1]);

                if ($this->isValidTimezone($timezone)) {
                    return $timezone;
                }
            }
        }

        // Fall back to UTC when the system timezone cannot be determined.
        return 'UTC';
    }

    private function isValidTimezone(string $timezone): bool
    {
        return $timezone !== '' && in_array($timezone, timezone_identifiers_list(), true);
    }
}