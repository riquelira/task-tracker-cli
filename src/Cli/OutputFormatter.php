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
            ? '🔍  Nenhuma tarefa encontrada.'
            : $this->beautify($task);
    }

    /** @param Task[] $tasks */
    public function tasks(array $tasks): string
    {
        if ($tasks === []) {
            return '🔍  Nenhuma tarefa encontrada.';
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
            "🕒  Criada em:     " . $task->createdAt?->format('d/m/Y H:i:s'),
            "✏️  Atualizada em: " . ($task->updatedAt?->format('d/m/Y H:i:s') ?? '—'),
        ]);
    }

    /** @param array<string|null> $lines  (null vira uma linha separadora) */
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

    /** Trunca ou completa com espaços até a largura da caixa. */
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

    /** Largura visual em colunas (cada caractere = 1; seletor de variação = 0). */
    private function width(string $text): int
    {
        return preg_match_all('/[^\x{FE00}-\x{FE0F}]/u', $text);
    }

    /**
     * @return array{0: string, 1: string} ícone e rótulo do status
     */
    private function statusBadge(TaskStatus $status): array
    {
        return match ($status) {
            TaskStatus::TODO        => ['🔴', 'A fazer'],
            TaskStatus::IN_PROGRESS => ['🟡', 'Em andamento'],
            TaskStatus::DONE        => ['🟢', 'Concluída'],
        };
    }
}
