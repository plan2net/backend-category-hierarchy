<?php

declare(strict_types=1);

namespace Plan2net\BackendCategoryHierarchy;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class CategoryLabelProcessor
{
    private const SEPARATOR = ' > ';

    /**
     * @see \TYPO3\CMS\Backend\Utility\BackendUtility::getRecordTitle
     */
    public function process(array &$parameters): void
    {
        $record = BackendUtility::getRecordWSOL('sys_category', (int)$parameters['row']['uid']);
        $currentTitle = $record['title'];
        // Display/List mode only
        if ($this->isEditMode()) {
            $parameters['title'] = $currentTitle;

            return;
        }

        $titles = $this->getCategoryParentTitlesRecursive(
            (int)$record['parent'],
            (int)$record['sys_language_uid']
        );
        $parameters['title'] = $this->composeCompleteTitle($titles, $currentTitle);
    }

    private function getCategoryParentTitlesRecursive(
        int $parentCategoryId,
        int $languageId = 0
    ): array {
        if (!$parentCategoryId) {
            return [];
        }

        $path = [[]];

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('sys_category');
        $result = $queryBuilder
            ->select('uid', 'parent', 'title')
            ->from('sys_category')
            ->where(
                $queryBuilder->expr()->eq(
                    'uid',
                    $queryBuilder->createNamedParameter($parentCategoryId)
                )
            )
            ->execute();
        while ($row = $result->fetch()) {
            $title = $row['title'];
            if (0 !== $languageId) {
                $title = $this->translateCategoryTitle((int)$row['uid'], $languageId);
            }

            $path[] = [$title];
            $path[] = $this->getCategoryParentTitlesRecursive((int)$row['parent'], $languageId);
        }

        return array_filter(array_merge(...$path));
    }

    private function composeCompleteTitle(array $titles, string $currentTitle): string
    {
        return $currentTitle . (count($titles) ?
            ' (' . trim(self::SEPARATOR . ' ' . implode(self::SEPARATOR, $titles)) . ')' : '');
    }

    private function translateCategoryTitle(int $categoryId, int $languageId): string
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('sys_category');
        $result = $queryBuilder
            ->select('title')
            ->from('sys_category')
            ->where(
                $queryBuilder->expr()->eq('sys_language_uid',
                    $queryBuilder->createNamedParameter($languageId, \PDO::PARAM_INT)),
                $queryBuilder->expr()->eq('l10n_parent',
                    $queryBuilder->createNamedParameter($categoryId, \PDO::PARAM_INT))

            )
            ->setMaxResults(1)
            ->execute()->fetch();

        return $result['title'] ?? '';
    }

    private function isEditMode(): bool
    {
        $arguments = GeneralUtility::_GET();

        return isset($arguments['route']) &&
            (
                '/record/edit' === $arguments['route'] ||
                '/ajax/record/tree/fetchData' === $arguments['route']
            );
    }
}
