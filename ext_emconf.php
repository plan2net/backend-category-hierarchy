<?php

$EM_CONF['backend_category_hierarchy'] = [
    'title' => 'Show category hierarchy information in list view',
    'description' => '',
    'category' => 'backend',
    'constraints' => [
        'depends' => [
            'typo3' => '13.4.0-14.99.99',
            'backend' => '13.4.0-14.99.99',
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
    'version' => '14.0.0',
];
