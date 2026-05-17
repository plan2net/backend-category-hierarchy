<?php

declare(strict_types=1);

namespace Plan2net\BackendCategoryHierarchy\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use Plan2net\BackendCategoryHierarchy\TitleFormatSettings;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class TitleFormatSettingsTest extends UnitTestCase
{
    #[Test]
    public function preservesValidInputs(): void
    {
        $settings = new TitleFormatSettings('{ancestors} > {current}', ' / ');

        self::assertSame('{ancestors} > {current}', $settings->template);
        self::assertSame(' / ', $settings->separator);
    }

    #[Test]
    public function fallsBackToDefaultTemplateWhenInputIsEmpty(): void
    {
        $settings = new TitleFormatSettings('', ' > ');

        self::assertSame(TitleFormatSettings::DEFAULT_TEMPLATE, $settings->template);
    }

    #[Test]
    public function fallsBackToDefaultTemplateWhenCurrentPlaceholderIsMissing(): void
    {
        $settings = new TitleFormatSettings('only {ancestors}', ' > ');

        self::assertSame(TitleFormatSettings::DEFAULT_TEMPLATE, $settings->template);
    }

    #[Test]
    public function fallsBackToDefaultTemplateWhenAncestorsPlaceholderIsMissing(): void
    {
        $settings = new TitleFormatSettings('only {current}', ' > ');

        self::assertSame(TitleFormatSettings::DEFAULT_TEMPLATE, $settings->template);
    }

    #[Test]
    public function fallsBackToDefaultSeparatorWhenInputIsEmpty(): void
    {
        $settings = new TitleFormatSettings('{current} ({ancestors})', '');

        self::assertSame(TitleFormatSettings::DEFAULT_SEPARATOR, $settings->separator);
    }

    #[Test]
    public function defaultsReturnsCanonicalValues(): void
    {
        $settings = TitleFormatSettings::defaults();

        self::assertSame(TitleFormatSettings::DEFAULT_TEMPLATE, $settings->template);
        self::assertSame(TitleFormatSettings::DEFAULT_SEPARATOR, $settings->separator);
        self::assertSame(TitleFormatSettings::DEFAULT_COMPACT_TEMPLATE, $settings->compactTemplate);
    }

    #[Test]
    public function fallsBackToDefaultCompactTemplateWhenInvalid(): void
    {
        $settings = new TitleFormatSettings('{ancestors} > {current}', ' > ', 'only {current}');

        self::assertSame(TitleFormatSettings::DEFAULT_COMPACT_TEMPLATE, $settings->compactTemplate);
    }

    #[Test]
    public function forCompactContextReturnsInstanceUsingCompactTemplate(): void
    {
        $settings = new TitleFormatSettings(
            '{ancestors} > {current}',
            ' / ',
            '{current} :: {ancestors}',
        );

        $compact = $settings->forCompactContext();

        self::assertSame('{current} :: {ancestors}', $compact->template);
        self::assertSame(' / ', $compact->separator);
    }
}
