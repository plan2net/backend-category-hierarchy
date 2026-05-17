<?php

declare(strict_types=1);

namespace Plan2net\BackendCategoryHierarchy;

use Doctrine\DBAL\Exception;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;

final class CategoryLabelProcessor
{
    private const TABLE = 'sys_category';
    private const MAX_DEPTH = 50;
    private const EDIT_MODE_ROUTE_PATHS = [
        '/record/edit',
        '/ajax/record/tree/fetchData',
    ];

    /** @var array<string, list<string>> */
    private array $chainCache = [];

    public function __construct(
        private readonly ConnectionPool $connectionPool,
        private readonly TitleFormatter $titleFormatter,
    ) {
    }

    /**
     * @param array{table?: string, row: array<string, mixed>, title?: string, options?: array<string, mixed>} $parameters
     *
     * @throws Exception
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
        $parameters['title'] = $this->titleFormatter->format($currentTitle, $ancestorTitles);
    }

    /**
     * @return list<string>
     *
     * @throws Exception
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

            $title = $languageId !== 0
                ? $this->fetchTranslatedTitle($row['uid'], $languageId)
                : $row['title'];
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
     *
     * @throws Exception
     */
    private function fetchCategoryRow(int $uid): ?array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(self::TABLE);
        $row = $queryBuilder
            ->select('uid', 'parent', 'title')
            ->from(self::TABLE)
            ->where(
                $queryBuilder->expr()->eq(
                    'uid',
                    $queryBuilder->createNamedParameter($uid, Connection::PARAM_INT)
                )
            )
            ->setMaxResults(1)
            ->executeQuery()
            ->fetchAssociative();
        if ($row === false) {
            return null;
        }

        return [
            'uid' => (int) $row['uid'],
            'parent' => (int) $row['parent'],
            'title' => (string) $row['title'],
        ];
    }

    /**
     * @throws Exception
     */
    private function fetchTranslatedTitle(int $categoryId, int $languageId): string
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(self::TABLE);
        $row = $queryBuilder
            ->select('title')
            ->from(self::TABLE)
            ->where(
                $queryBuilder->expr()->eq(
                    'sys_language_uid',
                    $queryBuilder->createNamedParameter($languageId, Connection::PARAM_INT)
                ),
                $queryBuilder->expr()->eq(
                    'l10n_parent',
                    $queryBuilder->createNamedParameter($categoryId, Connection::PARAM_INT)
                )
            )
            ->setMaxResults(1)
            ->executeQuery()
            ->fetchAssociative();

        return (string) ($row['title'] ?? '');
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
