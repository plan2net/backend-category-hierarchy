<?php

declare(strict_types=1);

namespace Plan2net\BackendCategoryHierarchy;

use Doctrine\DBAL\Exception;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;

final class AncestorChainBuilder
{
    private const TABLE = 'sys_category';
    private const MAX_DEPTH = 50;

    /** @var array<string, list<string>> */
    private array $cache = [];

    public function __construct(
        private readonly ConnectionPool $connectionPool,
    ) {
    }

    /**
     * @return list<string>
     *
     * @throws Exception
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

        // Single recursive CTE: walks the parent chain on default-language rows
        // and overlays the localized title via LEFT JOIN. Workspace overlay is
        // intentionally not applied here — chain structure and titles reflect
        // live data. The current record still uses BackendUtility::getRecordWSOL.
        $sql = \sprintf(
            'WITH RECURSIVE ancestors AS (
                SELECT uid, parent, title, 0 AS depth
                FROM %1$s
                WHERE uid = :startUid AND deleted = 0 AND sys_language_uid = 0
                UNION ALL
                SELECT c.uid, c.parent, c.title, a.depth + 1
                FROM %1$s c
                INNER JOIN ancestors a ON c.uid = a.parent
                WHERE a.depth < %2$d AND c.deleted = 0 AND c.sys_language_uid = 0
            )
            SELECT COALESCE(t.title, a.title) AS title
            FROM ancestors a
            LEFT JOIN %1$s t
                ON t.l10n_parent = a.uid
                AND t.sys_language_uid = :languageId
                AND t.deleted = 0
            ORDER BY a.depth ASC',
            self::TABLE,
            self::MAX_DEPTH,
        );

        $rows = $this->connectionPool
            ->getConnectionForTable(self::TABLE)
            ->executeQuery(
                $sql,
                ['startUid' => $startUid, 'languageId' => $languageId],
                ['startUid' => Connection::PARAM_INT, 'languageId' => Connection::PARAM_INT],
            )
            ->fetchAllAssociative();

        $chain = [];
        foreach ($rows as $row) {
            $title = (string) ($row['title'] ?? '');
            if ($title !== '') {
                $chain[] = $title;
            }
        }

        return $this->cache[$cacheKey] = $chain;
    }
}
