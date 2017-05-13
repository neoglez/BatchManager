<?php
return array(
    'router' => array(
        'routes' => array(
            'batch' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route' => '/batch[/:action][/:batchId]',
                    'defaults' => array(
                        'controller' => 'BatchManager\Controller\Batch',
                        'action' => 'index'
                    )
                )
            ),
        ),
    ),
    'service_manager' => array(
        'aliases' => array(
            // here we are aliasing for our purpose
            'batch_manager_zend_db_adapter' => 'Zend\Db\Adapter\Adapter',
        ),
    ),
    'controllers' => array(
        'factories' => array(
            'BatchManager\Controller\Batch' => 'BatchManager\Factory\BatchControllerServiceFactory',
        ),
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
    // Placeholder for console routes
    'console' => array(
        'router' => array(
            'routes' => array(
            ),
        ),
    ),
    'asset_manager' => array(
        'resolver_configs' => array(
            'paths' => array(
                __DIR__ . '/../public',
            ),
        ),
    ),
);
