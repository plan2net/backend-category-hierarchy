<?php

declare(strict_types=1);

namespace Plan2net\BackendCategoryHierarchy\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use Plan2net\BackendCategoryHierarchy\TitleFormatSettings;
use Plan2net\BackendCategoryHierarchy\TitleFormatter;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class TitleFormatterTest extends UnitTestCase
{
    #[Test]
    public function returnsCurrentTitleWhenAncestorListIsEmpty(): void
    {
        $formatter = new TitleFormatter();

        self::assertSame(
            'Java',
            $formatter->format('Java', [], TitleFormatSettings::defaults())
        );
    }

    #[Test]
    public function formatsWithDefaultTemplate(): void
    {
        $formatter = new TitleFormatter();

        self::assertSame(
            'Java (Programming > Topic)',
            $formatter->format('Java', ['Programming', 'Topic'], TitleFormatSettings::defaults())
        );
    }

    #[Test]
    public function formatsWithBreadcrumbTemplate(): void
    {
        $formatter = new TitleFormatter();
        $settings = new TitleFormatSettings('{ancestors} > {current}', ' > ');

        self::assertSame(
            'Programming > Topic > Java',
            $formatter->format('Java', ['Programming', 'Topic'], $settings)
        );
    }

    #[Test]
    public function honoursConfiguredSeparator(): void
    {
        $formatter = new TitleFormatter();
        $settings = new TitleFormatSettings('{current} ({ancestors})', ' / ');

        self::assertSame(
            'Java (Programming / Topic)',
            $formatter->format('Java', ['Programming', 'Topic'], $settings)
        );
    }
}
