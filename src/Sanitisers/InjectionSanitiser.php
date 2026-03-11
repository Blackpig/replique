<?php

namespace BlackpigCreatif\Replique\Sanitisers;

class InjectionSanitiser
{
    /**
     * Patterns known to be used in prompt-injection attacks.
     * Silently stripped rather than rejected — avoids revealing detection.
     *
     * @var array<string>
     */
    private const PATTERNS = [
        '/ignore previous instructions?/i',
        '/you are now/i',
        '/system\s*:/i',
        '/\[INST\]/i',
        '/<s>/i',
        '/assistant\s*:/i',
        '/forget your instructions/i',
    ];

    public function sanitise(string $text): string
    {
        return preg_replace(self::PATTERNS, '', $text) ?? $text;
    }
}
