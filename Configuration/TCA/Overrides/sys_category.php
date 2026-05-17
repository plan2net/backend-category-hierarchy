<?php

declare(strict_types=1);

use Plan2net\BackendCategoryHierarchy\CategoryLabelProcessor;

// This extension "suggests" `georgringer/news` in order to be able to overwrite this user function for categories.
$GLOBALS['TCA']['sys_category']['ctrl']['label_userFunc'] = CategoryLabelProcessor::class.'->process';
