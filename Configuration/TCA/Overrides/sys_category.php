<?php

defined('TYPO3_MODE') or die();

(static function () {
    // This extension "suggests" `georgringer/news` in order to be able to overwrite this user function for categories
    $GLOBALS['TCA']['sys_category']['ctrl']['label_userFunc'] =
        \Plan2net\BackendCategoryHierarchy\CategoryLabelProcessor::class . '->process';
})();
