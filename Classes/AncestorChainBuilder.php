<?php

declare(strict_types=1);

namespace Plan2net\BackendCategoryHierarchy;

use TYPO3\CMS\Backend\Domain\Repository\Localization\LocalizationRepository;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Context\Context;

final class AncestorChainBuilder
{
    private const TABLE = 'sys_category';
    private const MAX_DEPTH = 50;

    /** @var array<string, list<string>> */
    private array $cache = [];

    public function __construct(
        private readonly LocalizationRepository $localizationRepository,
        private readonly Context $context,
    ) {
    }

    /**
     * @return list<string>
     */
    public function build(int $startUid, int $languageId): array
    {
        if ($startUid === 0) {
            return [];
        }

        $cacheKey = $startUid.':'.$languageId;
        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
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

            $row = $this->loadCategory($currentUid);
            if ($row === null) {
                break;
            }

            $title = $row['title'];
            if ($languageId !== 0) {
                $localized = $this->localizedTitleOf($row['uid'], $languageId);
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

        return $this->cache[$cacheKey] = $chain;
    }

    /**
     * @return array{uid: int, parent: int, title: string}|null
     */
    private function loadCategory(int $uid): ?array
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

    private function localizedTitleOf(int $uid, int $languageId): string
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
}
