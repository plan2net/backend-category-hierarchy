<?php

declare(strict_types=1);

namespace Plan2net\BackendCategoryHierarchy;

final readonly class TitleFormatSettings
{
    public const DEFAULT_TEMPLATE = '{current} ({ancestors})';
    public const DEFAULT_SEPARATOR = ' > ';
    public const DEFAULT_COMPACT_TEMPLATE = '{current} ({ancestors})';

    public string $template;
    public string $separator;
    public string $compactTemplate;

    public function __construct(string $template, string $separator, string $compactTemplate = self::DEFAULT_COMPACT_TEMPLATE)
    {
        $this->template = self::isValidTemplate($template) ? $template : self::DEFAULT_TEMPLATE;
        $this->separator = '' !== $separator ? $separator : self::DEFAULT_SEPARATOR;
        $this->compactTemplate = self::isValidTemplate($compactTemplate) ? $compactTemplate : self::DEFAULT_COMPACT_TEMPLATE;
    }

    public static function defaults(): self
    {
        return new self(self::DEFAULT_TEMPLATE, self::DEFAULT_SEPARATOR, self::DEFAULT_COMPACT_TEMPLATE);
    }

    public function forCompactContext(): self
    {
        return new self($this->compactTemplate, $this->separator, $this->compactTemplate);
    }

    private static function isValidTemplate(string $template): bool
    {
        return str_contains($template, '{current}') && str_contains($template, '{ancestors}');
    }
}
