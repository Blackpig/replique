<?php

namespace BlackpigCreatif\Replique\Sanitisers;

use BlackpigCreatif\Replique\Enums\TextMode;
use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\DisallowedRawHtml\DisallowedRawHtmlExtension;
use League\CommonMark\Extension\ExternalLink\ExternalLinkExtension;

class TextSanitiser
{
    public function __construct(private readonly InjectionSanitiser $injectionSanitiser) {}

    public function process(string $text, TextMode $mode): string
    {
        if (config('replique.sanitise_injection', true)) {
            $text = $this->injectionSanitiser->sanitise($text);
        }

        return match ($mode) {
            TextMode::Plain => $this->processPlain($text),
            TextMode::EscapedHtml => $this->processEscapedHtml($text),
            TextMode::Markdown => $this->processMarkdown($text),
        };
    }

    private function processPlain(string $text): string
    {
        return strip_tags($text);
    }

    private function processEscapedHtml(string $text): string
    {
        return htmlspecialchars(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    private function processMarkdown(string $text): string
    {
        if (! class_exists(CommonMarkConverter::class)) {
            return $this->processEscapedHtml($text);
        }

        $environment = new Environment([
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
            'external_link' => [
                'internal_hosts' => config('app.url'),
                'open_in_new_window' => true,
                'html_class' => '',
                'nofollow' => 'external',
                'noopener' => 'external',
                'noreferrer' => 'external',
            ],
        ]);

        $environment->addExtension(new CommonMarkCoreExtension);
        $environment->addExtension(new DisallowedRawHtmlExtension);
        $environment->addExtension(new ExternalLinkExtension);

        $converter = new CommonMarkConverter(environment: $environment);

        return $converter->convert($text)->getContent();
    }
}
