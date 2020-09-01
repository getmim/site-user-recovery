<?php

return [
    '__name' => 'site-user-recovery',
    '__version' => '0.0.1',
    '__git' => 'git@github.com:getmim/site-user-recovery.git',
    '__license' => 'MIT',
    '__author' => [
        'name' => 'Iqbal Fauzi',
        'email' => 'iqbalfawz@gmail.com',
        'website' => 'https://iqbalfn.com/'
    ],
    '__files' => [
        'modules/site-user-recovery' => ['install','update','remove'],
        'theme/site/me/recovery' => ['install','remove'],
        'app/site-user-recovery' => ['install','remove']
    ],
    '__dependencies' => [
        'required' => [
            [
                'lib-user-recovery' => NULL
            ],
            [
                'site' => NULL
            ],
            [
                'lib-user' => NULL
            ]
        ],
        'optional' => []
    ],
    'autoload' => [
        'classes' => [
            'SiteUserRecovery\\Controller' => [
                'type' => 'file',
                'base' => 'app/site-user-recovery/controller'
            ],
            'SiteUserRecovery\\Library' => [
                'type' => 'file',
                'base' => 'modules/site-user-recovery/library'
            ]
        ],
        'files' => []
    ],
    'routes' => [
        'site' => [
            'siteMeRecovery' => [
                'path' => [
                    'value' => '/me/recovery'
                ],
                'handler' => 'SiteUserRecovery\\Controller\\Recovery::recovery',
                'method' => 'GET|POST'
            ],
            'siteMeRecoveryReset' => [
                'path' => [
                    'value' => '/me/recovery/reset/(:hash)',
                    'params'=> [
                        'hash' => 'any'
                    ]
                ],
                'handler' => 'SiteUserRecovery\\Controller\\Recovery::reset',
                'method' => 'GET|POST'
            ],
            'siteMeRecoveryResetResent' => [
                'path' => [
                    'value' => '/me/recovery/resent/(:user)/(:recover)',
                    'params'=> [
                        'user' => 'number',
                        'recover' => 'number'
                    ]
                ],
                'handler' => 'SiteUserRecovery\\Controller\\Recovery::resent',
                'method' => 'GET|POST'
            ]
        ]
    ],
    'libForm' => [
        'forms' => [
            'site.me.recovery' => [
                'identity' => [
                    'label' => 'Identity',
                    'type' => 'text',
                    'rules' => [
                        'required' => TRUE,
                        'empty' => FALSE
                    ]
                ]
            ],
            'site.me.reset' => [
                'password' => [
                    'label' => 'New Password',
                    'type' => 'password',
                    'rules' => [
                        'required' => true,
                        'empty' => false,
                        'length' => ['min' => 6]
                    ]
                ],
                're-password' => [
                    'label' => 'Retype Password',
                    'type' => 'password',
                    'rules' => [
                        'required' => true,
                        'empty' => false,
                        'equals_to' => 'password'
                    ]
                ]
            ]
        ]
    ]
];
