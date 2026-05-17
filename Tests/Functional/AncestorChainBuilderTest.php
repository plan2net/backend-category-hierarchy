<?php

declare(strict_types=1);

namespace Plan2net\BackendCategoryHierarchy\Tests\Functional;

use PHPUnit\Framework\Attributes\Test;
use Plan2net\BackendCategoryHierarchy\AncestorChainBuilder;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

final class AncestorChainBuilderTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = ['backend_category_hierarchy'];

    private AncestorChainBuilder $builder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->importCSVDataSet(__DIR__.'/Fixtures/sys_category.csv');
        $this->builder = $this->get(AncestorChainBuilder::class);
    }

    #[Test]
    public function returnsEmptyChainWhenStartUidIsZero(): void
    {
        self::assertSame([], $this->builder->build(0, 0));
    }

    #[Test]
    public function returnsEmptyChainForUnknownUid(): void
    {
        self::assertSame([], $this->builder->build(9999, 0));
    }

    #[Test]
    public function walksParentChainInDefaultLanguage(): void
    {
        self::assertSame(
            ['Programming', 'Topic'],
            $this->builder->build(2, 0)
        );
    }

    #[Test]
    public function fallsBackToDefaultLanguageWhenTranslationMissing(): void
    {
        // German requested: Programming has a translation, Topic does not
        self::assertSame(
            ['Programmierung', 'Topic'],
            $this->builder->build(2, 1)
        );
    }

    #[Test]
    public function includesHiddenAncestorInChain(): void
    {
        // 'Deprecated' (uid=5) is hidden but should still appear in the chain
        self::assertSame(
            ['Deprecated', 'Topic'],
            $this->builder->build(5, 0)
        );
    }

    #[Test]
    public function resolvesTranslatedSeedUidToDefaultLanguage(): void
    {
        // uid=10 is the German translation of Programming (uid=2);
        // build(10, 0) should still produce the default-language chain
        self::assertSame(
            ['Programming', 'Topic'],
            $this->builder->build(10, 0)
        );
    }
}
