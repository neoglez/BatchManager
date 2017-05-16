<?php
return [
    'router' => [
        'routes' => [
            'batch' => [
                'type' => \Zend\Router\Http\Segment::class,
                'options' => [
                    'route' => '/batch[/:action][/:batchId]',
                    'defaults' => [
                        'controller' => 'BatchManager\Controller\Batch',
                        'action' => 'index',
                    ],
                ],
            ],
        ],
    ],
    'service_manager' => [
        'aliases' => [
            // here we are aliasing for our purpose
            'batch_manager_zend_db_adapter' => 'Zend\Db\Adapter\Adapter',
        ],
    ],
    'controllers' => [
        'factories' => [
            'BatchManager\Controller\Batch' => 'BatchManager\Factory\BatchControllerServiceFactory',
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
    // Placeholder for console routes
    'console' => [
        'router' => [
            'routes' => [
            ],
        ],
    ],
    'asset_manager' => [
        'resolver_configs' => [
            'paths' => [
                __DIR__ . '/../public',
            ],
        ],
    ],
];
