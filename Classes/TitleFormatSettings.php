<?php

declare(strict_types=1);

namespace Plan2net\BackendCategoryHierarchy;

final readonly class TitleFormatSettings
{
    public const DEFAULT_TEMPLATE = '{current} ({ancestors})';
    public const DEFAULT_SEPARATOR = ' > ';

    public string $template;
    public string $separator;

    public function __construct(string $template, string $separator)
    {
        $this->template = self::isValidTemplate($template) ? $template : self::DEFAULT_TEMPLATE;
        $this->separator = $separator !== '' ? $separator : self::DEFAULT_SEPARATOR;
    }

    public static function defaults(): self
    {
        return new self(self::DEFAULT_TEMPLATE, self::DEFAULT_SEPARATOR);
    }

    private static function isValidTemplate(string $template): bool
    {
        return str_contains($template, '{current}') && str_contains($template, '{ancestors}');
    }
}
