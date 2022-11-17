<?php

$EM_CONF['backend_category_hierarchy'] = [
    'title' => 'Show category hierarchy information in list view',
    'description' => '',
    'category' => 'backend',
    'constraints' => [
        'depends' => [
            'typo3' => '',
        ],
        'suggests' => [
            'news' => '',
        ],
        'conflicts' => [
        ],
    ],
    'state' => 'stable',
    'clearCacheOnLoad' => 1,
    'author' => 'Wolfgang Klinger',
    'author_email' => 'wk@plan2.net',
    'author_company' => 'plan2net GmbH',
    'version' => '1.0.0',
];
