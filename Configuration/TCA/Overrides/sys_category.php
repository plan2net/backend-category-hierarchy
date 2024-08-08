<?php

use Plan2net\BackendCategoryHierarchy\CategoryLabelProcessor;

defined('TYPO3') or exit;

(static function () {
    // This extension "suggests" `georgringer/news` in order to be able to overwrite this user function for categories
    $GLOBALS['TCA']['sys_category']['ctrl']['label_userFunc'] = CategoryLabelProcessor::class . '->process';
})();
