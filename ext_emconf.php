<?php

$EM_CONF['backend_category_hierarchy'] = [
    'title' => 'Show category hierarchy information in list view',
    'description' => '',
    'category' => 'backend',
    'constraints' => [
        'depends' => [
            'typo3' => '12.4.0-12.4.99',
        ],
        'suggests' => [
            'news' => '',
        ],
        'conflicts' => [
        ],
    ],
    'state' => 'stable',
    'author' => 'Wolfgang Klinger',
    'author_email' => 'wk@plan2.net',
    'author_company' => 'plan2net GmbH',
    'version' => '12.0.0',
];
