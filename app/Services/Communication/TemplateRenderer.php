<?php

declare(strict_types=1);

namespace App\Services\Communication;

use App\Models\NotificationTemplate;

/**
 * Centralized template rendering for all communication channels.
 *
 * Templates use {{variable_name}} placeholders. Rendering is intentionally
 * simple and deterministic: unknown variables are left untouched so that
 * partial data never silently corrupts a message.
 */
class TemplateRenderer
{
    public function render(NotificationTemplate $template, array $variables): array
    {
        return [
            'subject' => $this->renderString((string) $template->subject, $variables),
            'title' => $this->renderString((string) $template->title, $variables),
            'body' => $this->renderString($template->body, $variables),
        ];
    }

    public function renderString(string $content, array $variables): string
    {
        if ($content === '') {
            return $content;
        }

        foreach ($variables as $key => $value) {
            $content = str_replace('{{'.$key.'}}', $this->stringify($value), $content);
        }

        return $content;
    }

    private function stringify(mixed $value): string
    {
        return match (true) {
            is_null($value) => '',
            is_bool($value) => $value ? '1' : '0',
            is_scalar($value) => (string) $value,
            default => json_encode($value) ?: '',
        };
    }
}