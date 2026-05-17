<?php

declare(strict_types=1);

namespace Plan2net\BackendCategoryHierarchy;

final readonly class TitleFormatter
{
    /**
     * @param list<string> $ancestorTitles
     */
    public function format(string $currentTitle, array $ancestorTitles, TitleFormatSettings $settings): string
    {
        if ($ancestorTitles === []) {
            return $currentTitle;
        }

        return strtr($settings->template, [
            '{current}' => $currentTitle,
            '{ancestors}' => implode($settings->separator, $ancestorTitles),
        ]);
    }
}
