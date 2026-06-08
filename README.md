# Task Tracker

A simple command-line task manager written in PHP.

## Requirements

- PHP 8.4+ (CLI)

## Installation

Register the `task-tracker` command by running the setup script from the project folder:

```bash
cd task-tracker
./setup.sh
source ~/.bashrc
```

The script adds an alias to your `~/.bashrc`. After `source ~/.bashrc` (or opening a new terminal), the `task-tracker` command is available from any directory.

> You can also run it directly without installing: `php task-tracker.php <action> ...`

## Usage

```bash
task-tracker <action> [arguments] [--debug|-d]
```

| Action | Arguments | Description |
|--------|-----------|-------------|
| `add` | `<title> <description>` | Create a new task |
| `update` | `<id> <title\|description\|status> <value>` | Update a single field of a task |
| `update-all` | `<title\|description\|status> <value>` | Update the field on all tasks |
| `delete` | `<id>` | Delete a task by id |
| `delete-all` | — | Delete all tasks |
| `list` | `<id>` | Show a task by id |
| `list-all` | `[key regex]` | List tasks (or filter by key/regex) |
| `storage` | `<json\|yaml\|xml\|csv>` | Choose how tasks are stored |
| `help` | — | Show the help message |

### Options

- `-h`, `--help` — show the help message
- `-d`, `--debug` — show error details (file, line, stack trace)

### Examples

```bash
task-tracker add "Buy bread" "At the corner bakery"
task-tracker list 1
task-tracker update 1 status 2
task-tracker list-all status 1
task-tracker storage json
```

## Storage

Tasks are stored in the `data/` folder inside the project. The format is chosen with
the `storage` command and persisted across runs (defaults to JSON):

```bash
task-tracker storage csv
```

Supported formats: **json**, **yaml**, **xml**, **csv**.

> Switching the storage type does **not** migrate existing tasks between formats.

https://roadmap.sh/projects/task-tracker