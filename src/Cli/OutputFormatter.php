<?php

namespace TaskTracker\Cli;

use TaskTracker\Domain\Entity\Enum\TaskStatus;
use TaskTracker\Domain\Entity\Task;

class OutputFormatter
{
    private const int WIDTH = 38;

    public function task(?Task $task): string
    {
        return $task === null
            ? '🔍  No tasks found.'
            : $this->beautify($task);
    }

    public function tasks(array $tasks): string
    {
        if ($tasks === []) {
            return '🔍  No tasks found.';
        }

        $output = implode(PHP_EOL . PHP_EOL, array_map($this->beautify(...), $tasks));
        $output .= PHP_EOL . PHP_EOL . 'Total: ' . count($tasks);

        return $output;
    }

    public function beautify(Task $task): string
    {
        [$icon, $label] = $this->statusBadge($task->status);

        return $this->box([
            "{$icon}  #{$task->id} · {$label}",
            null, // separador
            "📌  {$task->title}",
            "📝  {$task->description}",
            "🕒  Created at: " . $task->createdAt?->format('d/m/Y H:i:s'),
            "✏️  Updated at: " . ($task->updatedAt?->format('d/m/Y H:i:s') ?? '—'),
        ]);
    }

    private function box(array $lines): string
    {
        $rule = str_repeat('─', self::WIDTH + 2);

        $out = ['╭' . $rule . '╮'];
        foreach ($lines as $line) {
            $out[] = $line === null
                ? '├' . $rule . '┤'
                : '│ ' . $this->pad($line) . ' │';
        }
        $out[] = '╰' . $rule . '╯';

        return implode(PHP_EOL, $out);
    }

    private function pad(string $text): string
    {
        $width = $this->width($text);

        if ($width > self::WIDTH) {
            $chars = preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY) ?: [];
            $text = implode('', array_slice($chars, 0, self::WIDTH - 1)) . '…';
            $width = self::WIDTH;
        }

        return $text . str_repeat(' ', self::WIDTH - $width);
    }

    private function width(string $text): int
    {
        return preg_match_all('/[^\x{FE00}-\x{FE0F}]/u', $text);
    }

    private function statusBadge(TaskStatus $status): array
    {
        return match ($status) {
            TaskStatus::TODO        => ['🔴', 'To do'],
            TaskStatus::IN_PROGRESS => ['🟡', 'In progress'],
            TaskStatus::DONE        => ['🟢', 'Done'],
        };
    }
}
