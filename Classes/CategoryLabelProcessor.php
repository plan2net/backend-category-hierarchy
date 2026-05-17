<?php

declare(strict_types=1);

namespace Plan2net\BackendCategoryHierarchy;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Site\SiteFinder;

final class CategoryLabelProcessor
{
    private const TABLE = 'sys_category';
    private const SITE_CONFIG_KEY = 'backendCategoryHierarchy';
    private const EDIT_MODE_ROUTE_PATHS = [
        '/record/edit',
        '/ajax/record/tree/fetchData',
    ];
    private const COMPACT_ROUTE_PATHS = [
        '/ajax/livesearch/search',
    ];

    /** @var array<int, TitleFormatSettings> */
    private array $settingsCache = [];

    public function __construct(
        private readonly TitleFormatter $titleFormatter,
        private readonly AncestorChainBuilder $ancestorChainBuilder,
        private readonly SiteFinder $siteFinder,
    ) {
    }

    /**
     * @param array{table?: string, row: array<string, mixed>, title?: string, options?: array<string, mixed>} $parameters
     *
     * @see BackendUtility::getRecordTitle
     */
    public function process(array &$parameters): void
    {
        $record = BackendUtility::getRecordWSOL(self::TABLE, (int) $parameters['row']['uid']);
        $currentTitle = (string) ($record['title'] ?? '');
        if ($this->isEditMode()) {
            $parameters['title'] = $currentTitle;

            return;
        }

        $ancestorTitles = $this->ancestorChainBuilder->build(
            (int) ($record['parent'] ?? 0),
            (int) ($record['sys_language_uid'] ?? 0),
        );
        $settings = $this->resolveSettingsForPage((int) ($record['pid'] ?? 0));
        if ($this->isCompactContext()) {
            $settings = $settings->forCompactContext();
        }
        $parameters['title'] = $this->titleFormatter->format($currentTitle, $ancestorTitles, $settings);
    }

    private function resolveSettingsForPage(int $pageId): TitleFormatSettings
    {
        if (isset($this->settingsCache[$pageId])) {
            return $this->settingsCache[$pageId];
        }
        try {
            $configuration = $this->siteFinder->getSiteByPageId($pageId)->getConfiguration()[self::SITE_CONFIG_KEY] ?? [];
        } catch (SiteNotFoundException) {
            return $this->settingsCache[$pageId] = TitleFormatSettings::defaults();
        }

        return $this->settingsCache[$pageId] = new TitleFormatSettings(
            template: (string) ($configuration['titleTemplate'] ?? ''),
            separator: (string) ($configuration['ancestorSeparator'] ?? ''),
            compactTemplate: (string) ($configuration['compactTitleTemplate'] ?? ''),
        );
    }

    private function isEditMode(): bool
    {
        return $this->currentRouteMatches(self::EDIT_MODE_ROUTE_PATHS);
    }

    private function isCompactContext(): bool
    {
        return $this->currentRouteMatches(self::COMPACT_ROUTE_PATHS);
    }

    /**
     * @param list<string> $paths
     */
    private function currentRouteMatches(array $paths): bool
    {
        $request = $GLOBALS['TYPO3_REQUEST'] ?? null;
        if (!$request instanceof ServerRequestInterface) {
            return false;
        }
        $route = $request->getAttribute('route');
        if ($route === null) {
            return false;
        }

        return \in_array($route->getPath(), $paths, true);
    }
}
