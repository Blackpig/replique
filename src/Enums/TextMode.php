<?php

namespace BlackpigCreatif\Replique\Enums;

use Filament\Support\Contracts\HasLabel;

enum TextMode: string implements HasLabel
{
    case Plain = 'plain';
    case Markdown = 'markdown';
    case EscapedHtml = 'escaped_html';

    public function getLabel(): string
    {
        return match ($this) {
            self::Plain => 'Plain text',
            self::Markdown => 'Markdown',
            self::EscapedHtml => 'Escaped HTML',
        };
    }
}
