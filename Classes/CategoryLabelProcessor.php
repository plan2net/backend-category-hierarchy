<?php

declare(strict_types=1);

namespace Plan2net\BackendCategoryHierarchy;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Domain\Repository\Localization\LocalizationRepository;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Site\SiteFinder;

final class CategoryLabelProcessor
{
    private const TABLE = 'sys_category';
    private const MAX_DEPTH = 50;
    private const EDIT_MODE_ROUTE_PATHS = [
        '/record/edit',
        '/ajax/record/tree/fetchData',
    ];
    private const SITE_CONFIG_KEY = 'backendCategoryHierarchy';

    /** @var array<string, list<string>> */
    private array $chainCache = [];

    /** @var array<int, TitleFormatSettings> */
    private array $settingsCache = [];

    public function __construct(
        private readonly TitleFormatter $titleFormatter,
        private readonly LocalizationRepository $localizationRepository,
        private readonly Context $context,
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

        $ancestorTitles = $this->buildAncestorChain(
            (int) ($record['parent'] ?? 0),
            (int) ($record['sys_language_uid'] ?? 0),
        );
        $settings = $this->resolveSettingsForPage((int) ($record['pid'] ?? 0));
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
        );
    }

    /**
     * @return list<string>
     */
    private function buildAncestorChain(int $startUid, int $languageId): array
    {
        if ($startUid === 0) {
            return [];
        }

        $cacheKey = $startUid.':'.$languageId;
        if (isset($this->chainCache[$cacheKey])) {
            return $this->chainCache[$cacheKey];
        }

        $chain = [];
        $visited = [];
        $currentUid = $startUid;
        $depth = 0;
        while ($currentUid !== 0 && $depth < self::MAX_DEPTH) {
            if (isset($visited[$currentUid])) {
                break;
            }
            $visited[$currentUid] = true;

            $row = $this->fetchCategoryRow($currentUid);
            if ($row === null) {
                break;
            }

            $title = $row['title'];
            if ($languageId !== 0) {
                $localized = $this->fetchLocalizedTitle($row['uid'], $languageId);
                if ($localized !== '') {
                    $title = $localized;
                }
            }
            if ($title !== '') {
                $chain[] = $title;
            }
            $currentUid = $row['parent'];
            ++$depth;
        }

        return $this->chainCache[$cacheKey] = $chain;
    }

    /**
     * @return array{uid: int, parent: int, title: string}|null
     */
    private function fetchCategoryRow(int $uid): ?array
    {
        $record = BackendUtility::getRecordWSOL(self::TABLE, $uid);
        if (!\is_array($record)) {
            return null;
        }

        return [
            'uid' => (int) ($record['uid'] ?? 0),
            'parent' => (int) ($record['parent'] ?? 0),
            'title' => (string) ($record['title'] ?? ''),
        ];
    }

    private function fetchLocalizedTitle(int $uid, int $languageId): string
    {
        // @phpstan-ignore function.alreadyNarrowedType (TYPO3 v13 compatibility branch)
        if (method_exists($this->localizationRepository, 'getRecordTranslation')) {
            $workspaceId = (int) $this->context->getPropertyFromAspect('workspace', 'id', 0);
            $translation = $this->localizationRepository->getRecordTranslation(self::TABLE, $uid, $languageId, $workspaceId);
            /** @psalm-suppress InternalMethod */
            $title = $translation?->toArray()['title'] ?? '';

            return (string) $title;
        }
        /** @psalm-suppress DeprecatedMethod */
        $rows = BackendUtility::getRecordLocalization(self::TABLE, $uid, $languageId);

        return \is_array($rows) && isset($rows[0]['title']) ? (string) $rows[0]['title'] : '';
    }

    private function isEditMode(): bool
    {
        $request = $GLOBALS['TYPO3_REQUEST'] ?? null;
        if (!$request instanceof ServerRequestInterface) {
            return false;
        }
        $route = $request->getAttribute('route');
        if ($route === null) {
            return false;
        }
        $path = $route->getPath();

        return \in_array($path, self::EDIT_MODE_ROUTE_PATHS, true);
    }
}
