<?php

declare(strict_types=1);

namespace Plan2net\BackendCategoryHierarchy;

final readonly class TitleFormatter
{
    public function __construct(
        private string $separator = ' > ',
    ) {
    }

    /**
     * @param list<string> $ancestorTitles
     */
    public function format(string $currentTitle, array $ancestorTitles): string
    {
        if ($ancestorTitles === []) {
            return $currentTitle;
        }

        return $currentTitle.' ('.implode($this->separator, $ancestorTitles).')';
    }
}
