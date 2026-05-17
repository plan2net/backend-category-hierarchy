<?php

declare(strict_types=1);

namespace Plan2net\BackendCategoryHierarchy\Tests\Functional;

use GuzzleHttp\Psr7\ServerRequest;
use PHPUnit\Framework\Attributes\Test;
use Plan2net\BackendCategoryHierarchy\CategoryLabelProcessor;
use TYPO3\CMS\Backend\Routing\Route;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

final class CategoryLabelProcessorTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = ['backend_category_hierarchy'];

    private CategoryLabelProcessor $processor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->importCSVDataSet(__DIR__.'/Fixtures/sys_category.csv');
        $this->processor = $this->get(CategoryLabelProcessor::class);
    }

    #[Test]
    public function addsHierarchyOutsideEditContext(): void
    {
        $this->setRequestPath('/module/web/list');
        $parameters = ['row' => ['uid' => 3]];

        $this->processor->process($parameters);

        self::assertSame('Java (Programming > Topic)', $parameters['title'] ?? '');
    }

    #[Test]
    public function returnsPlainTitleOnRecordEdit(): void
    {
        $this->setRequestPath('/record/edit');
        $parameters = ['row' => ['uid' => 3]];

        $this->processor->process($parameters);

        self::assertSame('Java', $parameters['title'] ?? '');
    }

    #[Test]
    public function returnsPlainTitleOnFormSelectTreeFetch(): void
    {
        $this->setRequestPath('/ajax/record/tree/fetchData');
        $parameters = ['row' => ['uid' => 3]];

        $this->processor->process($parameters);

        self::assertSame('Java', $parameters['title'] ?? '');
    }

    private function setRequestPath(string $path): void
    {
        $request = (new ServerRequest('GET', 'https://example.com/typo3'.$path))
            ->withAttribute('route', new Route($path, []));
        $GLOBALS['TYPO3_REQUEST'] = $request;
    }
}
