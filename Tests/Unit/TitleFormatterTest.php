<?php

declare(strict_types=1);

namespace Plan2net\BackendCategoryHierarchy\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use Plan2net\BackendCategoryHierarchy\TitleFormatter;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class TitleFormatterTest extends UnitTestCase
{
    #[Test]
    public function returnsCurrentTitleWhenAncestorListIsEmpty(): void
    {
        $formatter = new TitleFormatter();

        self::assertSame('Sports', $formatter->format('Sports', []));
    }

    #[Test]
    public function appendsSingleAncestorInParentheses(): void
    {
        $formatter = new TitleFormatter();

        self::assertSame(
            'Tennis (Sports)',
            $formatter->format('Tennis', ['Sports'])
        );
    }

    #[Test]
    public function joinsAncestorChainWithDefaultSeparator(): void
    {
        $formatter = new TitleFormatter();

        self::assertSame(
            'Wimbledon (Tennis > Sports > Events)',
            $formatter->format('Wimbledon', ['Tennis', 'Sports', 'Events'])
        );
    }

    #[Test]
    public function honoursConfiguredSeparator(): void
    {
        $formatter = new TitleFormatter(' / ');

        self::assertSame(
            'Wimbledon (Tennis / Sports / Events)',
            $formatter->format('Wimbledon', ['Tennis', 'Sports', 'Events'])
        );
    }
}
