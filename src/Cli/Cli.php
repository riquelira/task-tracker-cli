<?php

namespace TaskTracker\Cli;

use InvalidArgumentException;
use TaskTracker\Infra\Storage\Enum\StorageType;

class Cli
{
    public array $args {
        get {
            return $this->args;
        }
        set {
            $this->args = $value;
        }
    }

    public string $action {
        get {
            return $this->action;
        }
        set {
            $this->action = $value;
        }
    }

    public array $actionArgs {
        get {
            return $this->actionArgs;
        }
        set {
            $this->actionArgs = $value;
        }
    }

    private array $validActions = [
        'add',          // task-tracker add <title> <description>
        'update',       // task-tracker update <id> [title|description|status] <value>
        'update-all',   // task-tracker update-all [title|description|status] <value>
        'delete',       // task-tracker delete <taskId>
        'delete-all',   // task-tracker delete-all
        'list',         // task-tracker list <taskId>
        'list-all',     // task-tracker list-all [key value]
        'storage'       // task-tracker storage <type>
    ];

    public function __construct(array $args)
    {
        array_shift($args); // Remove script name

        if ($this->isHelpRequest($args)) {
            $this->action = 'help';
            $this->actionArgs = [];
            $this->args = $args;
            return;
        }

        $this->validateArgs($args);
        $this->args = $args;
    }

    private function isHelpRequest(array $args): bool
    {
        return $args === []
            || $args[0] === 'help'
            || in_array('--help', $args, true)
            || in_array('-h', $args, true);
    }

    public function help(): string
    {
        $storageTypes = implode('|', StorageType::names());

        return <<<HELP
        Task Tracker — command-line task manager

        Usage:
          task-tracker <action> [arguments] [--debug|-d]

        Actions:
          add           <title> <description>                       Create a new task
          update        <id> <title|description|status> <value>     Update a single field of a task
          update-all    <title|description|status> <value>          Update the field on all tasks
          delete        <id>                                        Delete a task by id
          delete-all                                                Delete all tasks
          list          <id>                                        Show a task by id
          list-all      [key regex]                                 List tasks (or filter by key/regex)
          storage       <{$storageTypes}>                  Choose how tasks are stored (persisted)

        Options:
          -h, --help    Show this help message
          -d, --debug   Show error details (file, line, stack trace)

        Examples:
          task-tracker add "Buy bread" "At the corner bakery"
          task-tracker list 1
          task-tracker update 1 status 2
          task-tracker list-all status 1
          task-tracker storage json

        Note: switching the storage type does not migrate existing tasks between formats.
        HELP;
    }

    private function validateArgs(array $args): void
    {
        $action = (string)array_shift($args);
        $this->validateAction($action);
        $this->action = $action;

        $this->validateActionArgs($this->action, $args);
        $this->actionArgs = $args;
    }

    private function validateAction(string $action): void
    {
        if (!in_array($action, $this->validActions)) {
            throw new InvalidArgumentException("Invalid action: $action. Valid actions are: " . implode(', ', $this->validActions));
        }
    }

    private function validateActionArgs(string $action, array $args): void
    {
        if (str_contains($action, '-')) {
            $action = lcfirst(str_replace(' ', '', ucwords(str_replace('-', ' ', $action))));
        }

        $method = $action . 'ActionValidate';
        if (!method_exists($this, $method))
            throw new \BadMethodCallException("Validate method {$method} not found");

        $this->{$method}($args);
    }

    private function addActionValidate(array $actionArgs): void
    {
        if (count($actionArgs) != 2)
            throw new InvalidArgumentException("Add action requires at least two arguments: <title> <description>");

        $title = $actionArgs[0];
        if (!is_string($title))
            throw new InvalidArgumentException("Title must be a string");

        $description = $actionArgs[1];
        if (!is_string($description))
            throw new InvalidArgumentException("Description must be a string");
    }

    private function updateActionValidate(array $actionArgs): void
    {
        if (count($actionArgs) != 3)
            throw new InvalidArgumentException("Update action requires at least three arguments: <id> [title|description|status] <value>");

        $taskId = $actionArgs[0];
        if (!is_numeric($taskId))
            throw new InvalidArgumentException("Task id must be a number");

        $property = $actionArgs[1];
        if (!is_string($property))
            throw new InvalidArgumentException("Property to update must be a string");

        $validUpdateArgs = ['title', 'description', 'status'];
        if (!in_array($property, $validUpdateArgs))
            throw new InvalidArgumentException("Invalid update property. Allowed values: 'title', 'description', 'status'");

        $value = $actionArgs[2];
        if (!is_string($value))
            throw new InvalidArgumentException("Value to update must be a string");
    }

    private function updateAllActionValidate(array $actionArgs): void
    {
        if (count($actionArgs) != 2)
            throw new InvalidArgumentException("Update all action requires at least two arguments: [title|description|status] <value>");

        $property = $actionArgs[0];
        if (!is_string($property))
            throw new InvalidArgumentException("Property to update must be a string");

        $validUpdateArgs = ['title', 'description', 'status'];
        if (!in_array($property, $validUpdateArgs))
            throw new InvalidArgumentException("Invalid update property. Allowed values: 'title', 'description', 'status'");

        $value = $actionArgs[1];
        if (!is_string($value))
            throw new InvalidArgumentException("Value to update must be a string");
    }

    private function deleteActionValidate(array $actionArgs): void
    {
        if (count($actionArgs) != 1)
            throw new InvalidArgumentException("Delete action requires a task id");

        if (!is_numeric($actionArgs[0]))
            throw new InvalidArgumentException("Task id must be a number");
    }

    private function deleteAllActionValidate(array $actionArgs): void
    {
        if (count($actionArgs) != 0)
            throw new InvalidArgumentException("Delete all action does not require any arguments");
    }

    private function storageActionValidate(array $actionArgs): void
    {
        if (count($actionArgs) != 1)
            throw new InvalidArgumentException("Storage action requires exactly one argument: <type>");

        $type = $actionArgs[0];
        if (!is_string($type))
            throw new InvalidArgumentException("Storage type must be a string");

        $validTypes = StorageType::names();
        if (!in_array(strtolower($type), $validTypes, true))
            throw new InvalidArgumentException(
                "Invalid storage type. Allowed values: " . implode(', ', $validTypes)
            );
    }

    private function listActionValidate(array $actionArgs): void
    {
        if (count($actionArgs) != 1)
            throw new InvalidArgumentException("List action requires a task id as an argument or no arguments to list all tasks");

        if (!is_numeric($actionArgs[0]))
            throw new InvalidArgumentException("Task id must be a number");
    }

    private function isValidRegex(string $value): bool
    {
        return @preg_match("/$value/i", '') !== false;
    }

    private function listAllActionValidate(array $actionArgs): void
    {
        if (count($actionArgs) == 1)
            throw new InvalidArgumentException("List all requires a value to filter by if a key is provided");

        if (count($actionArgs) == 2) {
            $validListAllByArgs = ['title', 'description', 'status'];
            if (!in_array($actionArgs[0], $validListAllByArgs))
                throw new InvalidArgumentException("Invalid list all key. Allowed values: 'title', 'description', 'status'");

            $regex = $actionArgs[1];
            if (!is_string($regex))
                throw new InvalidArgumentException("List all key value must be a string (and regex)");

            if (!$this->isValidRegex($regex))
                throw new InvalidArgumentException("Invalid regex pattern: {$regex}");
        } elseif (count($actionArgs) > 2) {
            throw new InvalidArgumentException("List all by only requires a property and value to filter by");
        }
    }
}